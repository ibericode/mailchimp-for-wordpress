<?php

/**
* Takes care of requests to the MailChimp API
*
* @uses WP_HTTP
*/
class MC4WP_API {

	/**
	 * @var string
	 */
	private $api_url = 'https://api.mailchimp.com/2.0/';

	/**
	 * @var string
	 */
	private $api_key = '';

	/**
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * @var int
	 */
	protected $error_code = 0;

	/**
	 * @var boolean
	 */
	private $connected = null;

	/**
	 * @var object The full response object of the latest API call
	 */
	private $last_response;

	/**
	* Constructor
	*
	* @param string $api_key MailChimp API key
	*/
	public function __construct( $api_key ) {
		$this->api_key = $api_key;

		$dash_position = strpos( $api_key, '-' );
		if( $dash_position !== false ) {
			$this->api_url = 'https://' . substr( $api_key, $dash_position + 1 ) . '.api.mailchimp.com/2.0/';
		}
	}

	 /**
	 * Show an error message to administrators
	 *
	 * @param string $message
	 * @return bool
	 */
	private function show_error( $message ) {

		if( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		add_settings_error( 'mc4wp-api', 'mc4wp-api-error', $message, 'error' );
		return true;
	}

	/**
	* Pings the MailChimp API
	* Will store its result to ensure a maximum of 1 ping per page load
	*
	* @return boolean
	*/
	public function is_connected() {
		if( $this->connected === null ) {

			$this->connected = false;
			$result = $this->call( 'helper/ping' );

			if( $result !== false ) {
				if( isset( $result->msg ) && $result->msg === "Everything's Chimpy!" ) {
					$this->connected = true;
				} elseif( isset( $result->error ) ) {
					$this->show_error( 'MailChimp Error: ' . $result->error );
				}
			}

		}

		return $this->connected;
	}

	/**
	* Sends a subscription request to the MailChimp API
	*
	* @param string $list_id
	* @param string $email
	* @param array $merge_vars
	* @param string $email_type
	* @param boolean $double_optin
	* @param boolean $update_existing
	* @param boolean $replace_interests
	* @param boolean $send_welcome
	*
	* @return boolean Successful?
	*/
	public function subscribe( $list_id, $email, array $merge_vars = array(), $email_type = 'html', $double_optin = true, $update_existing = false, $replace_interests = true, $send_welcome = false ) {
		$data = array(
			'id' => $list_id,
			'email' => array( 'email' => $email),
			'merge_vars' => $merge_vars,
			'email_type' => $email_type,
			'double_optin' => $double_optin,
			'update_existing' => $update_existing,
			'replace_interests' => $replace_interests,
			'send_welcome' => $send_welcome,
		);

		$response = $this->call( 'lists/subscribe', $data );

		if( is_object( $response ) && isset( $response->email ) ) {
			return true;
		}

		return false;
	}

	/**
	* Gets the Groupings for a given List
	* @param string $list_id
	* @return array|boolean
	*/
	public function get_list_groupings( $list_id ) {
		$result = $this->call( 'lists/interest-groupings', array( 'id' => $list_id ) );

		if( is_array( $result ) ) {
			return $result;
		}

		return false;
	}

	/**
	 * @param array $list_ids Array of ID's of the lists to fetch. (optional)
	 *
	 * @return array|bool
	 */
	public function get_lists( $list_ids = array() ) {
		$args = array(
			'limit' => 100,
		);

		// set filter if the $list_ids parameter was set
		if( count( $list_ids ) > 0 ) {
			$args['filters'] = array(
				'list_id' => implode( ',', $list_ids ),
			);
		}

		$result = $this->call( 'lists/list', $args );

		if( is_object( $result ) && isset( $result->data ) ) {
			return $result->data;
		}

		return false;
	}

	/**
	* Get lists with their merge_vars for a given array of list id's
	 *
	* @param array $list_ids
	* @return array|boolean
	*/
	public function get_lists_with_merge_vars( $list_ids ) {
		$result = $this->call( 'lists/merge-vars', array('id' => $list_ids ) );

		if( is_object( $result ) && isset( $result->data ) ) {
			return $result->data;
		}

		return false;
	}

	/**
	* Gets the member info for one or multiple emails on a list
	*
	* @param string $list_id
	* @param array $emails
	* @return array|bool
	*/
	public function get_subscriber_info( $list_id, $emails ) {
		$result = $this->call( 'lists/member-info', array(
				'id' => $list_id,
				'emails'  => $emails,
			)
		);

		if( is_object( $result ) && isset( $result->data ) ) {
			return $result->data;
		}

		return false;
	}

	/**
	 * @param        $list_id
	 * @param array|string $email
	 * @param array  $merge_vars
	 * @param string $email_type
	 * @param bool   $replace_interests
	 *
	 * @return bool
	 */
	public function update_subscriber( $list_id, $email, $merge_vars = array(), $email_type = 'html', $replace_interests = false ) {

		// default to using email for updating
		if( ! is_array( $email ) ) {
			$email = array(
				'email' => $email,
			);
		}

		$result = $this->call( 'lists/update-member', array(
				'id' => $list_id,
				'email'  => $email,
				'merge_vars' => $merge_vars,
				'email_type' => $email_type,
				'replace_interests' => $replace_interests,
			)
		);

		if( is_object( $result ) ) {

			if( isset( $result->error ) ) {
				return false;
			} else {
				return true;
			}

		}

		return false;
	}

	/**
	* Checks if an email address is on a given list
	*
	* @param string $list_id
	* @param string $email
	* @return boolean
	*/
	public function list_has_subscriber( $list_id, $email ) {
		$member_info = $this->get_subscriber_info( $list_id, array( array( 'email' => $email ) ) );

		if( is_array( $member_info ) && isset( $member_info[0] ) ) {
			return ( $member_info[0]->status === 'subscribed' );
		}

		return false;
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

		if( ! is_array( $struct ) ) {
			// assume $struct is an email
			$struct = array(
				'email' => $struct,
			);
		}

		$response = $this->call( 'lists/unsubscribe', array(
				'id' => $list_id,
				'email' => $struct,
				'delete_member' => $delete_member,
				'send_goodbye' => $send_goodbye,
				'send_notify' => $send_notification,
			)
		);

		if( is_object( $response ) ) {
			if ( isset( $response->complete ) && $response->complete ) {
				return true;
			}
		}

		return false;
	}

	/**
	* Calls the MailChimp API
	*
	* @uses WP_HTTP
	*
	* @param string $method
	* @param array $data
	*
	* @return object
	*/
	public function call( $method, array $data = array() ) {

		$this->empty_last_response();

		// do not make request when no api key was provided.
		if( empty( $this->api_key ) ) {
			return false;
		}

		$data['apikey'] = $this->api_key;
		$url = $this->api_url . $method . '.json';

		$response = wp_remote_post( $url, array(
				'body' => $data,
				'timeout' => 15,
				'headers' => array(
					'Accept-Encoding' => '',
				),
				'sslverify' => false,
			)
		);

		// test for wp errors
		if( is_wp_error( $response ) ) {
			// show error message to admins
			$this->show_error( 'HTTP Error: ' . $response->get_error_message() );
			return false;
		}

		// dirty fix for older WP versions
		if( $method === 'helper/ping' && is_array( $response ) && isset( $response['headers']['content-length'] ) && (int) $response['headers']['content-length'] === 44 ) {
			return (object) array(
				'msg' => "Everything's Chimpy!",
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$response = json_decode( $body );

		// store response
		if( is_object( $response ) ) {
			$this->last_response = $response;

			if( isset( $response->error ) ) {
				$this->error_message = $response->error;
			}

			if( isset( $response->code ) ) {
				$this->error_code = (int) $response->code;
			}
		}

		return $response;
	}

	/**
	 * Checks if an error occurred in the most recent API request
	 *
	 * @return bool
	 */
	public function has_error() {
		return ( ! empty( $this->error_message ) );
	}

	/**
	* Get the most recent error message
	 *
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
	 * Empties all data from previous response
	 */
	private function empty_last_response() {
		$this->last_response = null;
		$this->error_code = 0;
		$this->error_message = '';
	}

}