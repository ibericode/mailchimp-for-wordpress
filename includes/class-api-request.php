<?php

/**
 * Class MC4WP_API_Request
 *
 * @api
 */
class MC4WP_API_Request {

	/**
	 * @var string Either "subscribe" or "unsubscribe"
	 */
	protected $type;

	/**
	 * @var string MailChimp list ID
	 */
	protected $list_id;

	/**
	 * @var string Email address
	 */
	protected $email;

	/**
	 * @var array Additional merge fields (optional, only for "subscribe" types)
	 */
	protected $merge_vars = array();

	/**
	 * @var array Additional config settings (optional)
	 */
	protected $config = array(
		'email_type' => 'html',
		'double_optin' => true,
		'send_welcome' => false,
		'update_existing' => false,
		'replace_interests' => false,
		'send_goodbye' => true,
		'send_notification' => false,
		'delete_member' => false
	);

	/**
	 * @var MC4WP_API_Response|null
	 */
	protected $response;

	/**
	 * @param       $type
	 * @param       $list_id
	 * @param       $email
	 * @param array $data
	 * @param array $config
	 */
	public function __construct( $type, $list_id, $email, array $data, array $config ) {
		$this->type = $type;
		$this->list_id = $list_id;
		$this->email = $email;
		$this->data = $data;
		$this->config = array_merge( $this->config, $config );
	}

	/**
	 * Process the request
	 *
	 * @return bool
	 */
	public function process() {

		$api = mc4wp_get_api();

		if( $this->type === 'subscribe' ) {
			$success = $api->subscribe( $this->list_id, $this->email, $this->merge_vars, $this->config['email_type'], $this->config['double_optin'], $this->config['update_existing'], $this->config['replace_interests'], $this->config['send_welcome'] );
		} else {
			$success = $api->unsubscribe( $this->list_id, $this->email, $this->config['send_goodbye'], $this->config['send_notification'], $this->config['delete_member'] );
		}

		$response = new MC4WP_API_Response( $this->type, $success, $api->get_last_response() );
		$this->response = $response;
		return $response;
	}


	/**
	 * Respond to the request
	 *
	 * @return bool
	 */
	public function get_response() {
		if( $this->response ) {
			return $this->response;
		}

		return null;
	}

	/**
	 * @param string $type
	 * @param string $email
	 * @param string $list_id
	 * @param array  $data
	 * @param array  $config
	 *
	 * @return iMC4WP_Request
	 */
	public static function create( $type, $list_id, $email, array $data, array $config ) {
		$request = new self( $type, $list_id, $email, $data, $config );
		return $request;
	}


	/**
	 * @return array
	 */
	public function __toArray() {
		return (array) $this;
	}

	/**
	 * @param $data
	 *
	 * @return MC4WP_API_Request
	 */
	public static function __fromArray( $data ) {
		$request = new self( $data['type'], $data['list_id'], $data['email'], $data['data'], $data['config'] );
		return $request;
	}


}