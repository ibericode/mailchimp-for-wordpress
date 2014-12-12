<?php

// prevent direct file access
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Registration_Form_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $type = 'registration_form';

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		add_action( 'register_form', array( $this, 'output_checkbox' ), 20 );
		add_action( 'user_register', array( $this, 'subscribe_from_registration' ), 90, 1 );
	}

	/**
	 * Subscribe from WP Registration form
	 *
	 * @param $user_id
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
		if ( ! is_object( $user ) || ! isset( $user->user_email ) ) {
			return false; 
		}

		$email = $user->user_email;
		$merge_vars = array( 'NAME' => $user->user_login );

		// try to add first name
		if ( isset( $user->first_name ) && !empty( $user->first_name ) ) {
			$merge_vars['FNAME'] = $user->first_name;
		}

		// try to add last name
		if ( isset( $user->last_name ) && !empty( $user->last_name ) ) {
			$merge_vars['LNAME'] = $user->last_name;
		}

		return $this->subscribe( $email, $merge_vars, $this->type );
	}
	/* End registration form functions */

}