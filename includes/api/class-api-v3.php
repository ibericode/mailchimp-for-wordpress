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


	/**
	 * @var array
	 */
	private $last_response;

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
	public function get( $resource, array $args = array() ) {
		return $this->request( 'GET', $resource, $args );
	}

	/**
	 * @param string $resource
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function post( $resource, array $data ) {
		return $this->request( 'POST', $resource, $data );
	}

	/**
	 * @param string $resource
	 * @param array $data
	 * @return mixed
	 */
	public function put( $resource, array $data ) {
		return $this->request( 'PUT', $resource, $data );
	}

	/**
	 * @param string $resource
	 * @param array $data
	 * @return mixed
	 */
	public function patch( $resource, array $data ) {
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
	private function request( $method, $resource, array $data = array() ) {
		$this->reset();

		$url = $this->api_url . ltrim( $resource, '/' );
		$args = array(
			'method' => $method,
			'headers' => $this->get_headers()
		);

		// attach arguments (in body or URL)
		if( $method === 'GET' ) {
			$url = add_query_arg( $data, $url );
		} else {
			$args['body'] = json_encode( $data );
		}

		// perform request
		$response = wp_remote_request( $url, $args );
		$this->last_response = $response;

		// parse response
		$data = $this->parse_response( $response );

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
	 * @return mixed
	 * 
	 * @throws MC4WP_API_Exception
	 */
	private function parse_response( $response ) {

		if( is_wp_error( $response ) ) {
			throw new MC4WP_API_Exception( 'Error connecting to MailChimp. ' . $response->get_error_message(), (int) $response->get_error_code() );
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

		if( $code >= 400 ) {
			if( $code === 404 ) {
				throw new MC4WP_API_Resource_Not_Found_Exception( $message, $code, $response, $data );
			}

			throw new MC4WP_API_Exception( $message, $code, $response , $data );
		}

		if( ! is_null( $data ) ) {
			return $data;
		}

		// TODO: Move this to user land
//		if( $code !== 200 ) {
//			$message = sprintf( 'The MailChimp API server returned the following response: <em>%s %s</em>.', $code, $message );
//
//			// check for Akamai firewall response
//			if( $code === 403 ) {
//				preg_match('/Reference (.*)/i', $body, $matches );
//
//				if( ! empty( $matches[1] ) ) {
//					$message .= '</strong><br /><br />' . sprintf( 'This usually means that your server is blacklisted by MailChimp\'s firewall. Please contact MailChimp support with the following reference number: %s </strong>', $matches[1] );
//				}
//			}
//
//
//		}

		// unable to decode response
		throw new MC4WP_API_Exception( $message, $code, $response );
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
	 * Get recent daily, aggregated activity stats for a list.
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/activity/#read-get_lists_list_id_activity
	 * @since 4.0
	 *
	 * @param string $list_id
	 *
	 * @return array
	 */
	public function get_list_activity( $list_id ) {
		$resource = sprintf( '/lists/%s/activity', $list_id );
		$data = $this->get( $resource );

		if( is_object( $data ) && isset( $data->activity ) ) {
			return $data->activity;
		}

		return array();
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

		// make sure we're sending an object as the MailChimp schema requires this
		if( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

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

		// make sure we're sending an object as the MailChimp schema requires this
		if( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

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
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#read-get_ecommerce_stores_store_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_ecommerce_store( $store_id, array $args = array() ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->get( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#create-post_ecommerce_stores
	 * @since 4.0
	 *
	 * @param string $store_id The unique identifier for the store.
	 * @param string $list_id The unique identifier for the MailChimp List associated with the store.
	 * @param string $name The name of the store.
	 * @param string $currency_code The three-letter ISO 4217 code for the currency that the store accepts.
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store( $store_id, $list_id, $name, $currency_code, array $args = array() ) {
		$resource = '/ecommerce/stores';
		$args['id'] = $store_id;
		$args['list_id'] = $list_id;
		$args['name'] = $name;
		$args['currency_code'] = $currency_code;
		return $this->post( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#edit-patch_ecommerce_stores_store_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function update_ecommerce_store( $store_id, array $args ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->patch( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#delete-delete_ecommerce_stores_store_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 *
	 * @return boolean
	 */
	public function delete_ecommerce_store( $store_id ) {
		$resource = sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->delete( $resource );
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
	 * Empties all data from previous response
	 */
	private function reset() {
		$this->last_response = null;
	}

	/**
	 * @return string
	 */
	public function get_last_response_body() {
		return wp_remote_retrieve_body( $this->last_response );
	}

	/**
	 * @return array
	 */
	public function get_last_response_headers() {
		return wp_remote_retrieve_headers( $this->last_response );
	}

	/**
	 * @return array|WP_Error
	 */
	public function get_last_response() {
		return $this->last_response;
	}


}