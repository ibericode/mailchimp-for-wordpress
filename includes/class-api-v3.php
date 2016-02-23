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
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function request( $method, $args = array() ) {

		$args['body'] = isset( $args['body'] ) ? $args['body'] : array();
		$args['headers'] = isset( $args['headers'] ) ? $args['headers'] : array();
		$args['headers']['Authorization'] = 'Basic ' . base64_encode( 'mc4wp:' . $this->api_key );
		$args['headers']['Accept'] = 'application/json';

		// Copy Accept-Language from browser headers
		if( ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$args['headers']['Accept-Language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}

		$response = wp_remote_request( $this->api_url . $method, $args );
		try {
			$data = $this->parse_response( $response );
		} catch( Exception $e ) {
			// TODO: Handle error
			return false;
		}

		return $data;
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
		$data = $this->request( '' );
		return is_object( $data ) && isset( $data->account_id );
	}

	/**
	 * Sends a subscription request to the MailChimp API
	 *
	 * @param string  $list_id           The list id to subscribe to
	 * @param string  $email             The email address to subscribe
	 * @param array   $merge_vars        Array of extra merge variables
	 * @param string  $email_type        The email type to send to this email address. Possible values are `html` and `text`.
	 * @param boolean $double_optin      Should this email be confirmed via double opt-in?
	 * @param boolean $update_existing   Update information if this email is already on list?
	 * @param boolean $replace_interests Replace interest groupings, only if update_existing is true.
	 * @param boolean $send_welcome      Send a welcome e-mail, only if double_optin is false.
	 *
	 * @return boolean|string True if success, 'error' if error
	 */
	public function subscribe( $list_id, $email, array $merge_vars = array(), $email_type = 'html', $double_optin = true, $update_existing = false, $replace_interests = true, $send_welcome = false ) {
		// TODO: Implement subscribe() method.
	}

	/**
	 * Gets the Groupings for a given List
	 *
	 * @param int $list_id
	 *
	 * @return array|boolean
	 */
	public function get_list_groupings( $list_id ) {
		// TODO: Implement get_list_groupings() method.
	}

	/**
	 * @param array $list_ids Array of ID's of the lists to fetch. (optional)
	 *
	 * @return bool
	 */
	public function get_lists( $list_ids = array() ) {
		// TODO: Implement get_lists() method.
	}

	/**
	 * Get the lists an email address is subscribed to
	 *
	 * @param array|string $email
	 *
	 * @return array
	 */
	public function get_lists_for_email( $email ) {
		// TODO: Implement get_lists_for_email() method.
	}

	/**
	 * Get lists with their merge_vars for a given array of list id's
	 *
	 * @param array $list_ids
	 *
	 * @return array|boolean
	 */
	public function get_lists_with_merge_vars( $list_ids ) {
		// TODO: Implement get_lists_with_merge_vars() method.
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
}}