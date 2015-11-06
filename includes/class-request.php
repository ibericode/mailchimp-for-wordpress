<?php

/**
 * Class MC4WP_Request
 *
 * @todo rework case-insensitive logic
 * @since 3.0
 * @ignore
 */
class MC4WP_Request {

	/**
	 * @var MC4WP_Array_Bag
	 */
	public $params;

	/**
	 * @var MC4WP_Array_Bag
	 */
	public $server;

	/**
	 * @var MC4WP_Request
	 */
	private static $instance;

	/**
	 * @return MC4WP_Request (the one created from global variables)
	 */
	public static function instance() {

		if( ! self::$instance instanceof self ) {
			self::$instance = self::create_from_globals();
		}

		return self::$instance;
	}


	/**
	 * @return MC4WP_Request
	 */
	public static function create_from_globals() {
		$params = array_merge( $_POST, $_GET );
		$params = stripslashes_deep( $params );
		$params = mc4wp_sanitize_deep( $params );
		return new self( $params, $_SERVER );
	}

	/**
	 * Constructor
	 *
	 * @param array $params
	 * @param array $server
	 */
	public function __construct( $params, $server = array() ) {
		$this->params = new MC4WP_Array_Bag( $params );
		$this->server = new MC4WP_Array_Bag( $server );
	}

	/**
	 * @return bool
	 */
	public function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * @param $method
	 *
	 * @return bool
	 */
	public function is_method( $method ) {
		return $this->server->get('REQUEST_METHOD') === $method;
	}

	/**
	 * @return string
	 */
	public function get_client_ip() {
		$headers = ( function_exists( 'apache_request_headers' ) ) ? apache_request_headers() : $this->server->all();

		if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return $headers['X-Forwarded-For'];
		}

		if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return $headers['HTTP_X_FORWARDED_FOR'];
		}

		return $this->server->get( 'REMOTE_ADDR' );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_param( $key, $default = null ) {
		return $this->params->get( $key, $default );
	}

	/**
	 * @param string $prefix
	 * @param int $case
	 *
	 * @return array
	 */
	public function get_all_params_without_prefix( $prefix, $case = null ) {
		return $this->params->all_without_prefix( $prefix, $case );
	}

	/**
	 * @param string $prefix
	 * @param int $case
	 *
	 * @return array
	 */
	public function get_all_params_with_prefix( $prefix, $case = null ) {
		return $this->params->all_with_prefix( $prefix, $case );
	}


}