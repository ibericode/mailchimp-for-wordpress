<?php

/**
 * Class MC4WP_Validator
 *
 * @ignore
 * @access private
 */
class MC4WP_Validator {

	/**
	 * @var array Array of fields to be validated.
	 */
	public $fields;

	/**
	 * @var array Array of validation rules.
	 */
	public $rules = array();

	/**
	 * @var array Array of error codes
	 */
	public $errors = array();

	/**
	 * @param array $fields
	 * @param array $rules
	 */
	public function __construct( array $fields, array $rules = array() ) {
		$this->fields = array_change_key_case( $fields, CASE_LOWER );
		$this->rules = $rules;
	}

	/**
	 * @param string $field
	 * @param string|callable $rule
	 * @param string $error_code Reference to the error
	 * @param array $config
	 */
	public function add_rule( $field, $rule, $error_code = 'error', $config = array() ) {
		$this->rules[] = array(
			'field' => $field,
			'rule' => $rule,
			'error_code' => $error_code,
			'config' => $config
		);
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function validate() {

		foreach( $this->rules as $rule ) {

			$value = $this->get_field_value( $rule['field'], '' );
			$method = 'is_' . $rule['rule'];

			if( ! method_exists( $this, $method ) ) {
				throw new Exception( sprintf( 'No validation rule "%s" exists.', $rule['rule'] ) );
			}

			$valid = call_user_func( array( $this, $method ), $value, $rule['config'] );

			if( ! $valid ) {
				if( ! in_array( $rule['error_code'], $this->errors ) ) {
					$this->errors[] = $rule['error_code'];
				}
			}
		}

		return count( $this->errors ) === 0;
	}

	/**
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function is_empty( $value ) {
		return empty( $value );
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function is_not_empty( $value ) {
		return ! $this->is_empty( $value );
	}

	/**
	 * @param $value
	 * @param $config
	 *
	 * @return false|int
	 */
	public function is_valid_nonce( $value, $config ) {

		// when using caching, assume valid nonce
		if( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return true;
		}

		return wp_verify_nonce( $value, $config['action'] ) !== false;
	}

	/**
	 * @param $value
	 * @param $config
	 *
	 * @return bool
	 */
	public function is_range( $value, $config ) {
		$value = intval( $value );

		if( isset( $config['min'] ) && $value < $config['min'] ) {
			return false;
		}

		if( isset( $config['max'] ) && $value > $config['max'] ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function is_email( $value ) {
		return is_string( $value ) && is_email( $value );
	}

	/**
	 * @param string $key
	 * @param string $default
	 *
	 * @return mixed
	 */
	private function get_field_value( $key, $default = '' ) {
		$key = strtolower( $key );
		$location = &$this->fields;

		foreach(explode('.', $key) as $step) {
			$location = &$location[$step];
		}

		return $location === null ? $default : $location;
	}

}