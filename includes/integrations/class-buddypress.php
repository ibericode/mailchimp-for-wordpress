<?php

// prevent direct file access
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_BuddyPress_Integration extends MC4WP_Integration {

	protected $type = 'buddypress_form';

	public function __construct() {

		parent::__construct();

		add_action( 'bp_before_registration_submit_buttons', array( $this, 'output_checkbox' ), 20 );
		add_action( 'bp_core_signup_user', array( $this, 'subscribe_from_buddypress' ), 10, 4 );
	}

	/**
	* Subscribes from BuddyPress Registration Form
	* @param int $user_id
	* @param string $user_login
	* @param string $user_password
	* @param string $user_email
	* @param array $usermeta
	*/
	public function subscribe_from_buddypress( $user_id, $user_login, $user_password, $user_email ) {
		
		if ( $this->checkbox_was_checked === false ) { 
			return false; 
		}

		// gather emailadress and name from user who BuddyPress registered
		$email = $user_email;

		$merge_vars = array(
			'NAME' => $user_login
		);

		return $this->subscribe( $email, $merge_vars, 'buddypress_registration' );
	}
	/* End BuddyPress functions */

}