<?php

// prevent direct file access
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Registration_Form_Integration extends MC4WP_User_Integration {

	protected $type = 'registration_form';

	public function __construct() {

		parent::__construct();

		add_action( 'register_form', array( $this, 'output_checkbox' ), 20 );
		add_action( 'user_register', array( $this, 'subscribe_from_registration' ), 90, 1 );
	}

	/**
	 * Subscribes from WP Registration Form
	 *
	 * @param int $user_id
	 *
	 * @return bool|string
	 */
	public function subscribe_from_registration( $user_id ) {

		if( $this->is_spam() ) {
			return false;
		}

		// was sign-up checkbox checked?
		if ( $this->checkbox_was_checked() === false ) {
			return false;
		}

		// gather emailadress from user who WordPress registered
		$user = get_userdata( $user_id );

		// was a user found with the given ID?
		if ( ! $user ) {
			return false;
		}

		$email = $user->user_email;
		$merge_vars = $this->user_merge_vars( $user );

		return $this->subscribe( $email, $merge_vars, 'registration', $user_id );
	}
	/* End registration form functions */

}