<?php

/**
 * Class MC4WP_API_v3
 *
 * TODO: Error handling
 */
class MC4WP_API_v3 implements iMC4WP_API {

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
	 * @return mixed
	 */
	public function get( $resource, $args = array() ) {
		return $this->request( 'GET', $resource, $args );
	}

	/**
	 * @param string $resource
	 * @param array $data
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
	 * TODO: Look at "replace_interests"
	 * TODO: Look at "send_welcome"
	 *
	 * Sends a subscription request to the MailChimp API
	 *
	 * @param string  $list_id           The list id to subscribe to
	 * @param string  $email_address             The email address to subscribe
	 * @param array   $merge_fields        Array of extra merge variables
	 * @param string  $email_type        The email type to send to this email address. Possible values are `html` and `text`.
	 * @param boolean $double_optin      Should this email be confirmed via double opt-in?
	 * @param boolean $update_existing   Update information if this email is already on list?
	 * @param boolean $replace_interests Unused. Replace interest groupings, only if update_existing is true.
	 * @param boolean $send_welcome      Unused. MailChimp deprecated this parameter in API v3.
	 *
	 * @return boolean
	 */
	public function subscribe( $list_id, $email_address, array $merge_fields = array(), $email_type = 'html', $double_optin = true, $update_existing = false, $replace_interests = true, $send_welcome = null ) {

		// first, check if subscriber is already on the given list
		$data = $this->get_list_member( $list_id, $email_address );

		if( is_object( $data ) && ! empty( $data->id ) ) {

			// email address is already subscribed
			if( $data->status === 'subscribed' ) {

				// should we update?
				if( $update_existing ) {
					return $this->update_subscriber($list_id, $email_address, $merge_fields, $email_type, $replace_interests);
				}

				// return old "already_subscribed" error
				// TODO: Maybe change this?
				$this->error_code = 214;
				return false;
			}

			// if double opt-in is enabled, try to delete email from list first.
			if( $double_optin ) {
				$success = $this->delete_list_member( $list_id, $email_address );

				// If this failed for some reason, assume success... Only difference is no new confirmation email (and maybe old details..)
				if( ! $success && $data->status === 'pending' ) {
					return true;
				}
			}
		}

		// not on list (or freshly deleted), subscribe.
		$status = $double_optin ? 'pending' : 'subscribed';
		$args = array(
			'email_address' => $email_address,
			'email_type' => $email_type,
			'status' => $status,
		);

		// for backwards compatibility, copy over OPTIN_IP from merge_fields array.
		// TODO: Decouple this from this method
		if( ! empty( $merge_fields[ 'OPTIN_IP' ] ) ) {
			$args['ip_signup'] = $merge_fields['OPTIN_IP'];
			unset( $merge_fields['OPTIN_IP'] );
		}

		// for backwards compatibility, copy over GROUPINGS from merge_fields array.
		if( ! empty( $merge_fields['GROUPINGS'] ) ) {
			$args['interests'] = $merge_fields['GROUPINGS'];
			unset( $merge_fields['GROUPINGS'] );
		}

		// remove "interests" key from merge vars
		if( ! empty( $merge_fields['INTERESTS'] ) ) {
			$args['interests'] = $merge_fields['INTERESTS'];
			unset( $merge_fields['INTERESTS'] );
		}

		// set leftover merge fields
		$args['merge_fields'] = $merge_fields;

		$data = $this->add_list_member( $list_id, $args );
		return is_object( $data ) && ! empty( $data->id );
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
	 * @deprecated 4.0
	 * @use MC4WP_API::update_list_member
	 *
	 * @param              $list_id
	 * @param array|string $email_address
	 * @param array        $merge_fields
	 * @param string       $email_type
	 * @param null         $replace_interests (unused)
	 *
	 * @return bool
	 */
	public function update_subscriber( $list_id, $email_address, $merge_fields = array(), $email_type = 'html', $replace_interests = null ) {
		_deprecated_function( __METHOD__, '4.0', 'MC4WP_API::update_list_member' );

		$args = array(
			'email_type' => $email_type,
			'status' => 'subscribed',
		);

		// for backwards compatibility, copy over OPTIN_IP from merge_fields array.
		// TODO: Decouple this from this method.

		// for backwards compatibility, copy over GROUPINGS from merge_fields array.
		if( ! empty( $merge_fields['GROUPINGS'] ) ) {
			$args['interests'] = $merge_fields['GROUPINGS'];
			unset( $merge_fields['GROUPINGS'] );
		}

		// remove "interests" key from merge vars
		if( ! empty( $merge_fields['INTERESTS'] ) ) {
			$args['interests'] = $merge_fields['INTERESTS'];
			unset( $merge_fields['INTERESTS'] );
		}

		// set leftover merge fields
		$args['merge_fields'] = $merge_fields;

		$data = $this->update_list_member( $list_id, $email_address, $args );

		return is_object( $data ) && ! empty( $data->id );
	}

	/**
	 * Unsubscribes the given email address from the given MailChimp list
	 *
	 * @deprecated 4.0
	 * @use MC4WP_API::update_list_member()
	 *
	 * @param string       $list_id
	 * @param string       $email_address
	 * @param null         $delete_member       unused
	 * @param null         $send_goodbye        unused
	 * @param null         $send_notification   unused
	 *
	 * @return bool
	 */
	public function unsubscribe( $list_id, $email_address, $send_goodbye = null, $send_notification = null, $delete_member = null ) {
		_deprecated_function( __METHOD__, '4.0', 'MC4WP_API::update_list_member()' );

		// for backwards compatibility with API v2 (which accepted an array)
		if( is_array( $email_address ) ) {
			$email_address = $email_address['email'];
		}

		$data = $this->update_list_member( $list_id, $email_address, array( 'status' => 'unsubscribed' ) );
		return is_object( $data ) && ! empty( $data->id );
	}

	/**
	 * Empties all data from previous response
	 */
	private function reset() {
		$this->last_response = null;
		$this->error_code = 0;
		$this->error_message = '';
	}

	/**
	 * @deprecated 4.0
	 * @use MC4WP_API::get_list_merge_fields
	 */
	public function get_list_merge_vars( $list_id ) {
		_deprecated_function( __METHOD__, '4.0', 'get_list_merge_fields' );
		return $this->get_list_merge_fields( $list_id );
	}

	/**
	 * @deprecated 4.0
	 * @use MC4WP_API::get_list_interest_categories
	 */
	public function get_list_groupings( $list_id ) {
		_deprecated_function( __METHOD__, '4.0', 'get_list_interest_categories' );
		return $this->get_list_interest_categories( $list_id );
	}

	/**
	 * Get the lists an email address is subscribed to
	 *
	 * @param array|string $email
	 *
	 * @return array
	 *
	 * @deprecated 4.0 This method was deprecated because of MailChimp API v3
	 */
	public function get_lists_for_email( $email ) {
		_deprecated_function( __METHOD__, '4.0' );
		return array();
	}

	/**
	 * Get lists with their merge_vars for a given array of list id's
	 *
	 * @param array $list_ids
	 * @return array
	 *
	 * @deprecated 4.0 This method was deprecated because of MailChimp API v3
	 * @see MC4WP_API_v3::get_list_merge_fields
	 */
	public function get_lists_with_merge_vars( $list_ids ) {
		_deprecated_function( __METHOD__, '4.0' );
		return array();
	}

	/**
	 * @deprecated 4.0
	 * @use MC4WP_API::add_ecommerce_store_order()
	 *
	 * @link https://apidocs.mailchimp.com/api/2.0/ecomm/order-add.php
	 *
	 * @param array $order_data
	 *
	 * @return boolean
	 */
	public function add_ecommerce_order( array $order_data ) {
		_deprecated_function( __METHOD__, '4.0', 'MC4WP_API::add_ecommerce_store_order()' );

		// get store id
		$store_id = $order_data['store_id'];

		// generate new $order_data format
		$old = $order_data;
		$order_data = array(
			'id' => $old['id'],
			'customer' => array(
				'id' => '', 				// TODO: Generate (or find) customer ID
				'email_address' => $old['email'],
			),
			'currency_code' => '',
			'order_total' => $old['total'],
			'tax_total' => $old['tax'],
			'lines' => array(),
			'processed_at_foreign' => $old['order_date'],
		);

		foreach( $old['items'] as $index => $item ) {
			$line_id = sprintf( '%s-%s', $order_data['id'], $index + 1 );
			$order_data['lines'][] = array(
				'id' => $line_id,
				'product_id' => $item['product_id'],
				'product_variant_id' => '',     // TODO: Look at what value we need for this...
				'quantity' => $item['qty'],
				'price' => $item['cost']
			);
		}

		if( isset( $old['campaign_id'] ) ) {
			$order_data['campaign_id'] = $old['campaign_id'];
		}

		return $this->add_ecommerce_store_order( $store_id, $order_data );
	}

	/**
	 *
	 * @deprecated 4.0
	 * @use MC4WP_API::delete_ecommerce_store_order()
	 *
	 * @link https://apidocs.mailchimp.com/api/2.0/ecomm/order-del.php
	 *
	 * @param string $store_id
	 * @param string $order_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_order( $store_id, $order_id ) {
		_deprecated_function( __METHOD__, '4.0', 'MC4WP_API::delete_ecommerce_store_order()' );
		return $this->delete_ecommerce_store_order( $store_id, $order_id );
	}

	/**
	 * Gets the member info for one or multiple emails on a list
	 *
	 * @deprecated 4.0
	 * @use MC4WP_API::get_list_member()
	 *
	 * @param string $list_id
	 * @param string $email
	 *
	 * @return array
	 */
	public function get_subscriber_info( $list_id, $email ) {

		_deprecated_function( __METHOD__, '4.0', 'MC4WP_API::get_list_member()' );

		if( is_array( $email ) ) {
			$email = array_shift( $email );
		}

		return $this->get_list_member( $list_id, $email );
	}

}