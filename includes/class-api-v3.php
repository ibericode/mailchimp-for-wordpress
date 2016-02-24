<?php

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
	 * @param string $method
	 * @param string $resource
	 * @param array $data
	 *
	 * @return mixed
	 */
	private function request( $method, $resource, $data = array() ) {

		$url = $this->api_url . ltrim( $resource, '/' );
		$args = array(
			'method' => $method,
			'headers' => $this->get_headers(),
			'body' => json_encode( $data ),
		);

		$response = wp_remote_request( $url, $args );

		try {
			$data = $this->parse_response( $response );
		} catch( Exception $e ) {
			// TODO: Handle error
			return false;
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
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );
		if( ! is_null( $data ) ) {
			return $data;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );

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
			$data = $this->request( 'GET', '/' );
			$this->connected = is_object( $data ) && isset( $data->account_id );
		}

		return $this->connected;
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 */
	public function get_email_address_hash( $email_address ) {
		return md5( strtolower( trim( $email_address ) ) );
	}

	/**
	 * Sends a subscription request to the MailChimp API
	 *
	 * @param string  $list_id           The list id to subscribe to
	 * @param string  $email_address             The email address to subscribe
	 * @param array   $merge_fields        Array of extra merge variables
	 * @param string  $email_type        The email type to send to this email address. Possible values are `html` and `text`.
	 * @param boolean $double_optin      Should this email be confirmed via double opt-in?
	 * @param boolean $update_existing   Update information if this email is already on list?
	 * @param boolean $replace_interests Replace interest groupings, only if update_existing is true.
	 * @param boolean $send_welcome      Unused. MailChimp deprecated this parameter in API v3.
	 *
	 * @return boolean
	 */
	public function subscribe( $list_id, $email_address, array $merge_fields = array(), $email_type = 'html', $double_optin = true, $update_existing = false, $replace_interests = true, $send_welcome = null ) {

		$email_address_hash = $this->get_email_address_hash( $email_address );

		// first, check if subscriber is already on the given list
		$data = $this->request( 'GET', sprintf( '/lists/%s/members/%s', $list_id, $email_address_hash ) );
		if( is_object( $data ) && ! empty( $data->id ) ) {

			// email address is already subscribed, should we update?
			if( $update_existing ) {
				return $this->update_subscriber( $list_id, $email_address, $merge_fields, $email_type, $replace_interests );
			}

			// TODO: Pass "already_subscribed" error here.

			return true;
		}

		// not on list, subscribe.
		$status = $double_optin ? 'pending' : 'subscribed';
		$args = array(
			'email_address' => $email_address,
			'email_type' => $email_type,
			'status' => $status,
		);

		// for backwards compatibility, copy over OPTIN_IP from merge_fields array.
		if( ! empty( $merge_fields[ 'OPTIN_IP' ] ) ) {
			$args['ip_signup'] = $merge_fields['OPTIN_IP'];
			unset( $merge_fields['OPTIN_IP'] );
		}

		// for backwards compatibility, copy over GROUPINGS from merge_fields array.
		if( ! empty( $merge_fields['GROUPINGS'] ) ) {
			$args['interests'] = $merge_fields['GROUPINGS'];
			unset( $merge_fields['GROUPINGS'] );
		}



		// set leftover merge fields
		$args['merge_fields'] = $merge_fields;
		$data = $this->request( 'POST', sprintf( '/lists/%s/members', $list_id ), $args );

		return is_object( $data ) && ! empty( $data->id );
	}

	/**
	 * Gets the Groupings for a given List
	 *
	 * @since 4.0
	 *
	 * @param string $list_id
	 *
	 * @return array
	 */
	public function get_list_interest_categories( $list_id ) {
		$data = $this->request( 'GET', sprintf( '/lists/%s/interest-categories', $list_id ) );

		if( is_object( $data ) && isset( $data->categories ) ) {
			return $data->categories;
		}

		return array();
	}

	/**
	 * @since 4.0
	 *
	 * @param $list_id
	 * @param $interest_category_id
	 *
	 * @return array
	 */
	public function get_list_interest_category_interests( $list_id, $interest_category_id ) {
		$resource = sprintf( '/lists/%s/interest-categories/%s/interests', $list_id, $interest_category_id );
		$data = $this->request( 'GET', $resource );

		if( is_object( $data ) && isset( $data->interests ) ) {
			return $data->interests;
		}

		return array();
	}

	/**
	 * Get merge vars for a given list
	 *
	 * @since 4.0
	 * @param string $list_id
	 * @return array
	 */
	public function get_list_merge_fields( $list_id ) {
		$data = $this->request( 'GET', sprintf( '/lists/%s/merge-fields', $list_id ) );

		if( is_object( $data ) && isset( $data->merge_fields ) ) {
			return $data->merge_fields;
		}

		return array();
	}

	/**
	 * @param array $list_ids Deprecated parameter.
	 *
	 * @return array
	 */
	public function get_lists( $list_ids = array() ) {
		$data = $this->request( 'GET', '/lists' );

		if( is_object( $data ) && isset( $data->lists ) ) {
			return $data->lists;
		}

		return array();
	}



	/**
	 * Gets the member info for one or multiple emails on a list
	 *
	 * @param string $list_id
	 * @param array  $emails
	 *
	 * @return array
	 */
	public function get_subscriber_info( $list_id, array $emails ) {
		// TODO: Implement get_subscriber_info() method.
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * Checks if an email address is on a given list
	 *
	 * @param string $list_id
	 * @param string $email
	 *
	 * @return boolean
	 */
	public function list_has_subscriber( $list_id, $email ) {
		// TODO: Implement list_has_subscriber() method.
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * @param              $list_id
	 * @param array|string $email
	 * @param array        $merge_vars
	 * @param string       $email_type
	 * @param bool         $replace_interests
	 *
	 * @return bool
	 */
	public function update_subscriber( $list_id, $email, $merge_vars = array(), $email_type = 'html', $replace_interests = false ) {
		// TODO: Implement update_subscriber() method.
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * Unsubscribes the given email or luid from the given MailChimp list
	 *
	 * @param string       $list_id
	 * @param array|string $struct
	 * @param bool         $delete_member
	 * @param bool         $send_goodbye
	 * @param bool         $send_notification
	 *
	 * @return bool
	 */
	public function unsubscribe( $list_id, $struct, $send_goodbye = true, $send_notification = false, $delete_member = false ) {
		// TODO: Implement unsubscribe() method.
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * @see https://apidocs.mailchimp.com/api/2.0/ecomm/order-add.php
	 *
	 * @param array $order_data
	 *
	 * @return boolean
	 */
	public function add_ecommerce_order( array $order_data ) {
		// TODO: Implement add_ecommerce_order() method.
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * @see https://apidocs.mailchimp.com/api/2.0/ecomm/order-del.php
	 *
	 * @param string $store_id
	 * @param string $order_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_order( $store_id, $order_id ) {
		// TODO: Implement delete_ecommerce_order() method.
		_deprecated_function( __METHOD__, '4.0' );
	}

	/**
	 * Checks if an error occured in the most recent request
	 * @return boolean
	 */
	public function has_error() {
		// TODO: Implement has_error() method.
	}

	/**
	 * Gets the most recent error message
	 * @return string
	 */
	public function get_error_message() {
		// TODO: Implement get_error_message() method.
	}

	/**
	 * Gets the most recent error code
	 *
	 * @return int
	 */
	public function get_error_code() {
		// TODO: Implement get_error_code() method.
	}

	/**
	 * Get the most recent response object
	 *
	 * @return object
	 */
	public function get_last_response() {
		// TODO: Implement get_last_response() method.
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

}