<?php

/**
 * Class MC4WP_Request
 *
 * @since 3.0
 */
class MC4WP_Request {

	/**
	 * @var array
	 */
	protected $raw_data;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var array
	 */
	protected $server;

	/**
	 * @return MC4WP_Request
	 */
	public static function create_from_globals() {
		return new self( $_REQUEST, $_SERVER );
	}

	/**
	 * Constructor
	 *
	 * @param array $data
	 * @param array $server
	 */
	public function __construct( $data, $server = array() ) {
		$this->raw_data = $data;
		$this->data = $this->normalize_data( $data );
		$this->server = $server;
	}

	/**
	 * @return array
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * @return array
	 */
	public function keys() {
		return array_keys( $this->data );
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $name, $default = '' ) {

		$name = strtoupper( $name );

		if( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}

		return $default;
	}

	/**
	 * @param $prefix
	 *
	 * @return array
	 */
	public function get_with_prefix( $prefix ) {
		$return = array();
		$prefix = strtoupper( $prefix );
		$length = strlen( $prefix );

		foreach( $this->data as $key => $value ) {
			if( strpos( $key, $prefix ) === 0 ) {

				$new_key = substr( $key, $length );
				$return[ $new_key ] = $value;
			}
		}

		return $return;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function normalize_data( array $data ) {

		// uppercase all data keys
		$data = array_change_key_case( $data, CASE_UPPER );

		// strip slashes on everything
		$data = stripslashes_deep( $data );

		// sanitize all the things
		$data = $this->sanitize_deep( $data );

		return (array) $data;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function sanitize_deep( $value ) {

		if ( is_scalar( $value ) ) {
			$value = sanitize_text_field( $value );
		} elseif( is_array( $value ) ) {
			$value = array_map( array( $this, 'sanitize_deep' ), $value );
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
				$value->{$key} = $this->sanitize_deep( $data );
			}
		}

		return $value;
	}

	/**
	 * @return bool
	 */
	public function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

}