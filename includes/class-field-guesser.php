<?php

/**
 * Class MC4WP_Field_Guesser
 *
 * @access private
 * @ignore
 */
class MC4WP_Field_Guesser {

	/**
	 * @var MC4WP_Array_Bag
	 */
	protected $fields;

	/**
	 * @param array $fields
	 */
	public function __construct( array $fields ) {
		$fields = array_change_key_case( $fields, CASE_UPPER );
		$this->fields = new MC4WP_Array_Bag( $fields );
	}

	/**
	 * Get all data which is namespaced with a given namespace
	 *
	 * @param string $namespace
	 *
	 * @return array
	 */
	public function namespaced( $namespace = 'mc4wp-' ) {
		$namespace = strtoupper( $namespace );
		return $this->fields->all_with_prefix( $namespace );
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

		$fields = $this->fields->all();

		foreach( $fields as $field => $value ) {

            // transform value into array to support 1-level arrays
            $sub_fields = is_array( $value ) ? $value : array( $value );

            foreach( $sub_fields as $sub_field_value ) {

                // poor man's urldecode, to get Enfold theme's contact element to work.
                $sub_field_value = str_replace( '%40', '@', $sub_field_value );

                // is this an email value? if so, assume it's the EMAIL field
                if( empty( $guessed['EMAIL'] ) && is_string( $sub_field_value ) && is_email( $sub_field_value ) ) {
                    $guessed['EMAIL'] = $sub_field_value;
                    continue 2;
                }

                // remove special characters from field name
                $simple_key = str_replace( array( '-', '_', ' ' ), '', $field );

                if( empty( $guessed['FNAME'] ) && $this->string_contains( $simple_key, array( 'FIRSTNAME', 'FNAME', 'GIVENNAME', 'FORENAME' ) ) ) {
                    // find first name field
                    $guessed['FNAME'] = $sub_field_value;
                } elseif( empty( $guessed['LNAME'] ) && $this->string_contains( $simple_key, array( 'LASTNAME', 'LNAME', 'SURNAME', 'FAMILYNAME' ) ) ) {
                    // find last name field
                    $guessed['LNAME'] = $sub_field_value;
                } elseif( empty( $guessed['NAME'] ) && $this->string_contains( $simple_key, 'NAME' ) ){
                    // find name field
                    $guessed['NAME'] = $sub_field_value;
                }
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

	/**
	 * @param $haystack
	 * @param $needles
	 *
	 * @return bool
	 */
	private function string_contains( $haystack, $needles ) {

		if( ! is_array( $needles ) ) {
			$needles = array( $needles );
		}

		foreach( $needles as $needle ) {

			if( strpos( $haystack, $needle ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
