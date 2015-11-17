<?php

/**
 * Class MC4WP_Request_Parser
 *
 * @ignore
 */
class MC4WP_Request_Parser {

	/**
	 * @var MC4WP_Request $request
	 */
	protected $request;

	/**
	 * @param MC4WP_Request|null $request
	 */
	public function __construct( $request = null ) {

		if( ! $request instanceof MC4WP_Request ) {
			$request = MC4WP_Request::create_from_globals();
		}

		$this->request = $request;
	}

	/**
	 * @return array
	 */
	public function all() {
		return $this->request->params->all();
	}

	/**
	 * Get all data which is namespaced with a given namespace
	 *
	 * @param string $namespace
	 *
	 * @return array
	 */
	public function namespaced( $namespace = 'mc4wp-' ) {
		// TODO: Find an easy way to make prefix case insensitive?
		return $this->request->params->all_with_prefix( $namespace );
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

		$fields = $this->request->params->all();
		$fields = array_change_key_case( $fields, CASE_UPPER );

		foreach( $fields as $field => $value ) {

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