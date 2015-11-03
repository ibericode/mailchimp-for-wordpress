<?php

/**
 * Class MC4WP_Request
 *
 * @since 3.0
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

}