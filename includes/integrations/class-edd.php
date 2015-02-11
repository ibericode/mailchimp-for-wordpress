<?php

// prevent direct file access
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_EDD_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $type = 'edd_checkout';

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'edd_purchase_form_user_info', array( $this, 'output_checkbox' ) );
		add_action( 'edd_payment_meta', array( $this, 'save_checkbox_value' ) );
		add_action( 'edd_complete_purchase', array( $this, 'subscribe_from_edd'), 50 );
	}

	/**
	 * @param array $meta
	 *
	 * @return array
	 */
	public function save_checkbox_value( $meta ) {

		// don't save anything if the checkbox was not checked
		if( ! $this->checkbox_was_checked() ) {
			return $meta;
		}

		$meta['_mc4wp_optin'] = 1;
		return $meta;
	}

	/**
	 * @param int $payment_id The ID of the payment
	 *
	 * @return bool|string
	 */
	public function subscribe_from_edd( $payment_id ) {

		$meta = edd_get_payment_meta( $payment_id );

		if( ! is_array( $meta ) || ! isset( $meta['_mc4wp_optin'] ) || ! $meta['_mc4wp_optin'] ) {
			return false;
		}

		$email = (string) edd_get_payment_user_email( $payment_id );
		$merge_vars = array();

		// add first and last name to merge vars, if given
		$user_info = (array) edd_get_payment_meta_user_info( $payment_id );

		if( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
			$merge_vars['NAME'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		}

		if( isset( $user_info['first_name'] ) ) {
			$merge_vars['FNAME'] = $user_info['first_name'];
		}

		if( isset( $user_info['last_name'] ) ) {
			$merge_vars['LNAME'] = $user_info['last_name'];
		}



		return $this->subscribe( $email, $merge_vars, $this->type, $payment_id );
	}

}

