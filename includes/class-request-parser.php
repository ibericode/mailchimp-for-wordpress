<?php

class MC4WP_Data_Parser {

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var string
	 */
	protected $namespace = 'mc4wp-';

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data = array_change_key_case( $data, CASE_UPPER );
	}

	/**
	 * @return array
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * Get all data which is namespaced with a given namespace
	 *
	 * @param string $namespace
	 *
	 * @return array
	 */
	public function namespaced( $namespace = 'MC4WP-' ) {
		$namespaced_data = array();

		// make sure namespace is uppercased, as is the field key
		$namespace = strtoupper( $namespace );

		foreach( $this->data as $field => $value ) {
			if( strpos( $field, $namespace ) === 0 ) {
				// get field name without namespace prefix
				$field = substr( $field, strlen( $namespace ) );
				$namespaced_data[ $field ] = $value;
			}
		}

		return $namespaced_data;
	}

	/**
	 * Guess values for the following fields
	 *  - EMAIL
	 *  - NAME
	 *  - FNAME
	 *  - LNAME
	 *
	 * @return array
	 */
	public function guessed() {
		$guessed = array();

		foreach( $this->data as $field => $value ) {

			// is this an email value? assume email field
			if( empty( $guessed['EMAIL'] ) && is_string( $value ) && is_email( $value ) ) {
				$guessed['EMAIL'] = $value;
				continue;
			}

			// remove special characters from field name
			$simple_key = str_replace( array( '-', '_' ), '', $field );

			if( empty( $guessed['NAME'] ) && in_array( $simple_key, array( 'NAME', 'YOURNAME', 'USERNAME', 'FULLNAME', 'CONTACTNAME' ) ) ) {
				// find name field
				$guessed['NAME'] = $value;
			} elseif( empty( $guessed['FNAME'] ) && in_array( $simple_key, array( 'FIRSTNAME', 'FNAME', 'GIVENNAME', 'FORENAME' ) ) ) {
				// find first name field
				$guessed['FNAME'] = $value;
			} elseif( empty( $guessed['LNAME'] ) && in_array( $simple_key, array( 'LASTNAME', 'LNAME', 'SURNAME', 'FAMILYNAME' ) ) ) {
				// find last name field
				$guessed['LNAME'] = $value;
			}

		}

		return $guessed;
	}

	/**
	 * @param $methods
	 *
	 * @return array
	 */
	public function combine( $methods ) {
		$combined = array();

		foreach( $methods as $method ) {
			if( method_exists( $this, $method ) ) {
				$combined = array_merge( $combined, call_user_func( array( $this, $method ) ) );
			}
		}

		return $combined;
	}
}