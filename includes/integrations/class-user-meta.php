<?php
// ------------------------------------------------------
//
// User Meta Integration for MC4WP
// By Elliot Lewis, @elliotlewis
// 14/10/24	-	v1
//
// ------------------------------------------------------

// prevent direct file access
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_User_Meta_Integration extends MC4WP_Integration {

	protected $type = 'user_meta';

	public function __construct() {

		add_action( 'user_meta_after_user_register', array( $this, 'mc4wp_user_meta_after_user_register' ), 90, 1 );

	}

	/**
	* Subscribes from User Meta Registration Form
	*
	* @param obj $response ID = user_id, plus all submitted fields
	*/
	public function mc4wp_user_meta_after_user_register( $response ) {

		//mc4wp-subscribe, _mc4wp_subscribe_user_meta
		// was sign-up checkbox checked?
//		if ( $this->checkbox_was_checked() === false ) { 
//			return false;
//		}
		if( !property_exists( $response, 'mc4wp-subscribe' ) || $response->{'mc4wp-subscribe'} != 'y' )
		{
			return false;
		}

		// gather email address from user who WordPress registered
		if( property_exists( $response, 'ID' ) )
		{
			$user = get_userdata( $response->ID );
		}
		else
		{
			return false;
		}

		// was a user found with the given ID?
		if ( ! $user ) { 
			return false; 
		}

		$email = $user->user_email;
		$merge_vars = array( 'NAME' => $user->user_login );

		// try to add first name
		if ( isset( $user->user_firstname ) && !empty( $user->user_firstname ) ) {
			$merge_vars['FNAME'] = $user->user_firstname;
		}

		// try to add last name
		if ( isset( $user->user_lastname ) && !empty( $user->user_lastname ) ) {
			$merge_vars['LNAME'] = $user->user_lastname;
		}

		return $this->subscribe( $email, $merge_vars, 'registration' );
	}
	/* End registration form functions */

}