<?php

/**
 * Class MC4WP_API_v3
 */
class MC4WP_API_v3 {

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var string
	 */
	private $api_url = 'https://api.mailchimp.com/3.0/';

	/**
	 * @var bool Are we able to talk to the MailChimp API?
	 */
	private $connected;

	private $error_message;
	private $error_code;
	private $last_response;
	private $last_response_raw;

	/**
	 * Constructor
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;

		$dash_position = strpos( $api_key, '-' );
		if( $dash_position !== false ) {
			$this->api_url = str_replace( '//api.', '//' . substr( $api_key, $dash_position + 1 ) . ".api.", $this->api_url );
		}
	}

	/**
	 * @param string $resource
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function get( $resource, $args = array() ) {
		return $this->request( 'GET', $resource, $args );
	}

	/**
	 * @param string $resource
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function post( $resource, $data = array() ) {
		return $this->request( 'POST', $resource, $data );
	}

	/**
	 * @param string $resource
	 * @param array $data
	 * @return mixed
	 */
	public function put( $resource, $data = array() ) {
		return $this->request( 'PUT', $resource, $data );
	}

	/**
	 * @param string $resource
	 * @param array $data
	 * @return mixed
	 */
	public function patch( $resource, $data = array() ) {
		return $this->request( 'PATCH', $resource, $data );
	}

	/**
	 * @param string $resource
	 * @return mixed
	 */
	public function delete( $resource ) {
		return $this->request( 'DELETE', $resource );
	}

	/**
	 * @param string $method
	 * @param string $resource
	 * @param array $data
	 *
	 * @return mixed
	 */
	private function request( $method, $resource, $data = array() ) {

		$this->reset();

		$url = $this->api_url . ltrim( $resource, '/' );
		$args = array(
			'method' => $method,
			'headers' => $this->get_headers(),
		);

		// attach arguments (in body or URL)
		if( $method === 'GET' ) {
			$url = add_query_arg( $data, $url );
		} else {
			$args['body'] = json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		try {
			$data = $this->parse_response( $response );
		} catch( Exception $e ) {
			$this->error_code = $e->getCode();
			$this->error_message = $e->getMessage();
			return false;
		}

		// store response
		$this->last_response_raw = $response;
		$this->last_response = $data;

		// store error (if any)
		if( is_object( $data ) ) {
			if( ! empty( $data->title ) ) {
				$this->error_message = $data->title;
			}

			// store error code (if any)
			if( ! empty( $data->status ) ) {
				$this->error_code = (int) $data->status;
			}
		}

		return $data;
	}

	/**
	 * @return array
	 */
	private function get_headers() {
		$headers = array();
		$headers['Authorization'] = 'Basic ' . base64_encode( 'mc4wp:' . $this->api_key );
		$headers['Accept'] = 'application/json';
		$headers['Content-Type'] = 'application/json';

		// Copy Accept-Language from browser headers
		if( ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$headers['Accept-Language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}

		return $headers;
	}

	/**
	 * @param array|WP_Error $response
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	private function parse_response( $response ) {
		if( is_wp_error( $response ) ) {
			throw new Exception( 'Error connecting to MailChimp. ' . $response->get_error_message(), (int) $response->get_error_code() );
		}

		// decode response body
		$code = (int) wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );
		$body = wp_remote_retrieve_body( $response );

		// set body to "true" in case MailChimp returned No Content
		if( $code < 300 && empty( $body ) ) {
			$body = "true";
		}

		$data = json_decode( $body );
		if( ! is_null( $data ) ) {
			return $data;
		}

		if( $code !== 200 ) {
			$message = sprintf( 'The MailChimp API server returned the following response: <em>%s %s</em>.', $code, $message );

			// check for Akamai firewall response
			if( $code === 403 ) {
				preg_match('/Reference (.*)/i', $body, $matches );

				if( ! empty( $matches[1] ) ) {
					$message .= '</strong><br /><br />' . sprintf( 'This usually means that your server is blacklisted by MailChimp\'s firewall. Please contact MailChimp support with the following reference number: %s </strong>', $matches[1] );
				}
			}
		}

		throw new Exception( $message, $code );
	}

	/**
	 * Pings the MailChimp API to see if we're connected
	 *
	 * The result is cached to ensure a maximum of 1 API call per page load
	 *
	 * @return boolean
	 */
	public function is_connected() {

		if( is_null( $this->connected ) ) {
			$data = $this->get( '/' );
			$this->connected = is_object( $data ) && isset( $data->account_id );
		}

		return $this->connected;
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 */
	public function get_subscriber_hash( $email_address ) {
		return md5( strtolower( trim( $email_address ) ) );
	}

	/**
	 *
	 * TODO: Force re-sending double opt-in email by deleting pending subscribers from list first.
	 * TODO: Move this method out of API class?
	 *
	 * Sends a subscription request to the MailChimp API
	 *
	 * @param string  $list_id           The list id to subscribe to
	 * @param string  $email_address             The email address to subscribe
	 * @param array    $args
	 * @param boolean $update_existing   Update information if this email is already on list?
	 * @param boolean $replace_interests Replace interest groupings, only if update_existing is true.
	 *
	 * @return boolean
	 */
	public function list_subscribe( $list_id, $email_address, array $args = array(), $update_existing = false, $replace_interests = true ) {

		$default_args = array(
			'status' => 'pending',
			'email_address' => $email_address
		);

		// setup default args
		$args = $args + $default_args;

		// first, check if subscriber is already on the given list
		$existing_member_data = $this->get_list_member( $list_id, $email_address );
		$existing_member = is_object( $existing_member_data ) && ! empty( $existing_member_data->id );

		// does this subscriber exist yet?
		if(  $existing_member && $existing_member_data->status === 'subscribed' ) {

			// if we're not supposed to update, bail.
			if( ! $update_existing ) {
				$this->error_code = 214;
				return false;
			}

			$args['status'] = 'subscribed';

			$existing_interests = (array) $existing_member_data->interests;

			// if replace, assume all existing interests disabled
			if( $replace_interests ) {
				$existing_interests = array_fill_keys( array_keys( $existing_interests ), false );
			}

			$args['interests'] = $args['interests'] + $existing_interests;
		}

//		// for backwards compatibility, copy over GROUPINGS from merge_fields array.
//		if( ! empty( $merge_fields['GROUPINGS'] ) ) {
//
//			// backwards compatibility for old interest groupings
//			$map = get_option( 'mc4wp_groupings_map', array() );
//			$interests = array();
//			foreach( $merge_fields['GROUPINGS'] as $grouping ) {
//				if( isset( $map[ $grouping['id'] ] ) ) {
//					$interests[ $map[ $grouping['id'] ] ] = true;
//				}
//			}
//
//			$args['interests'] = $interests;
//			unset( $merge_fields['GROUPINGS'] );
//		}

		return $this->add_list_member( $list_id, $args );
	}

	/**
	 * TODO: Move this method out of API class?
	 *
	 * @param string $list_id
	 * @param string $email_address
	 * @return object
	 */
	public function list_unsubscribe( $list_id, $email_address ) {
		return $this->update_list_member( $list_id, $email_address, array( 'status' => 'unsubscribed' ) );
	}

	/**
	 * Gets the Groupings for a given List
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/#read-get_lists_list_id_interest_categories
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_interest_categories( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/interest-categories', $list_id );
		$data = $this->get( $resource, $args );

		if( is_object( $data ) && isset( $data->categories ) ) {
			return $data->categories;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/interests/#read-get_lists_list_id_interest_categories_interest_category_id_interests
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param string $interest_category_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_interest_category_interests( $list_id, $interest_category_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/interest-categories/%s/interests', $list_id, $interest_category_id );
		$data = $this->get( $resource, $args );

		if( is_object( $data ) && isset( $data->interests ) ) {
			return $data->interests;
		}

		return array();
	}

	/**
	 * Get merge vars for a given list
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#read-get_lists_list_id_merge_fields
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_merge_fields( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/merge-fields', $list_id );
		$data = $this->get( $resource, $args );

		if( is_object( $data ) && isset( $data->merge_fields ) ) {
			return $data->merge_fields;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_list( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s', $list_id );
		$data = $this->get( $resource, $args );
		return $data;
	}

	/**
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_lists( $args = array() ) {
		$resource = '/lists';
		$data = $this->get( $resource, $args );

		if( is_object( $data ) && isset( $data->lists ) ) {
			return $data->lists;
		}

		return array();
	}

	/**
	 * Checks if an email address is on a given list with status "subscribed"
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return boolean
	 */
	public function list_has_subscriber( $list_id, $email_address ) {
		$data = $this->get_list_member( $list_id, $email_address );
		return is_object( $data ) && ! empty( $data->id ) && $data->status === 'subscribed';
	}



	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param string $email_address
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_list_member( $list_id, $email_address, array $args = array() ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->get( $resource, $args );
		return $data;
	}

	/**
	 * Add or update (!) a member to a MailChimp list.
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_list_member( $list_id, array $args ) {
		$subscriber_hash = $this->get_subscriber_hash( $args['email_address'] );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );

		// "put" updates the member if it's already on the list... take notice
		$data = $this->put( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	 * @since 4.0
	 *
	 * @param $list_id
	 * @param $email_address
	 * @param array $args
	 *
	 * @return object
	 */
	public function update_list_member( $list_id, $email_address, array $args = array() ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->patch( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return bool
	 */
	public function delete_list_member( $list_id, $email_address ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->delete( $resource );
		return !!$data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#create-post_ecommerce_stores_store_id_orders
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return boolean
	 */
	public function add_ecommerce_store_order( $store_id, array $args ) {
		$data = $this->post( sprintf( '/ecommerce/stores/%s/orders', $store_id ), $args );
		return is_object( $data ) && ! empty( $data->id );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#delete-delete_ecommerce_stores_store_id_orders_order_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param string $order_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_store_order( $store_id, $order_id ) {
		$data = $this->delete( sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id ) );
		return !! $data;
	}

	/**
	 * Checks if an error occured in the most recent request
	 *
	 * @return boolean
	 */
	public function has_error() {
		return ! empty( $this->error_message );
	}

	/**
	 * Gets the most recent error message
	 * @return string
	 */
	public function get_error_message() {
		return $this->error_message;
	}

	/**
	 * Gets the most recent error code
	 *
	 * @return int
	 */
	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * Get the most recent response object
	 *
	 * @return object
	 */
	public function get_last_response() {
		return $this->last_response;
	}

	/**
	 * Get the most recent response object (raw)
	 *
	 * @return object
	 */
	public function get_last_response_raw() {
		return $this->last_response_raw;
	}

	/**
	 * Empties all data from previous response
	 */
	private function reset() {
		$this->last_response = null;
		$this->error_code = 0;
		$this->error_message = '';
	}



}