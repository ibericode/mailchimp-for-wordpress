<?php

class MC4WP_Form_Validator {

	/**
	 * @var array
	 */
	protected $data = array();


	/**
	 * @param $data Array of fields with their values
	 */
	public function __construct( $data ) {
		$this->data = $data;
	}

	/**
	 * @param $data
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * Validate form nonce
	 *
	 * @return bool
	 */
	public function validate_nonce() {
		// detect caching plugin
		$using_caching = ( defined( 'WP_CACHE' ) && WP_CACHE );

		// validate form nonce, but only if not using caching
		if ( ! $using_caching && ( ! isset( $this->data['_MC4WP_FORM_NONCE'] ) || ! wp_verify_nonce( $this->data['_MC4WP_FORM_NONCE'], '_mc4wp_form_nonce' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Ensure honeypot is given but not filled
	 *
	 * @return bool
	 */
	public function validate_honeypot() {
		// ensure honeypot was given but not filled
		if ( ! isset( $this->data['_MC4WP_REQUIRED_BUT_NOT_REALLY'] ) || '' !== $this->data['_MC4WP_REQUIRED_BUT_NOT_REALLY'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate form timestamp, should be at least 1.5 seconds in the past
	 *
	 * @return bool
	 */
	public function validate_timestamp() {
		// check timestamp difference, token should be generated at least 2 seconds before form submit
		if( ! isset( $this->data['_MC4WP_TIMESTAMP'] ) || time() < ( intval( $this->data['_MC4WP_TIMESTAMP'] ) + 1.5 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate Captcha, if form had one.
	 *
	 * @return bool
	 */
	public function validate_captcha() {
		// check if captcha was present and valid
		if( isset( $this->data['_MC4WP_HAS_CAPTCHA'] ) && $this->data['_MC4WP_HAS_CAPTCHA'] == 1 && function_exists( 'cptch_check_custom_form' ) && cptch_check_custom_form() !== true ) {
			return false;
		}

		return true;
	}

	/**
	 * Ensure email address is given and valid
	 *
	 * @return bool
	 */
	public function validate_email() {
		// validate email
		if( ! isset( $this->data['EMAIL'] ) || ! is_string( $this->data['EMAIL'] ) || ! is_email( $this->data['EMAIL'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Ensure a list is selected or submitted
	 *
	 * @param $lists
	 *
	 * @return bool
	 */
	public function validate_lists( array $lists ) {
		return ! empty( $lists );
	}

	/**
	 * Run custom validation filter, should return true if valid, or an error code string when invalid
	 *
	 * @return string|bool
	 */
	public function custom_validation() {
		/**
		 * @filter mc4wp_valid_form_request
		 *
		 * Use this to perform custom form validation.
		 * Return true if the form is valid or an error string if it isn't.
		 * Use the `mc4wp_form_messages` filter to register custom error messages.
		 */
		$valid_form_request = apply_filters( 'mc4wp_valid_form_request', true, $this->data );

		return $valid_form_request;
	}


}