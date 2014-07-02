<?php

// prevent direct file access
if( ! defined("MC4WP_LITE_VERSION" ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_MultiSite_Integration extends MC4WP_Integration {

	protected $type = 'multisite_form';

	public function __construct() {

		parent::__construct();

		add_action( 'signup_extra_fields', array( $this, 'output_checkbox' ), 20 );
		add_action( 'signup_blogform', array( $this, 'add_multisite_hidden_checkbox' ), 20 );
		add_action( 'wpmu_activate_blog', array( $this, 'on_multisite_blog_signup' ), 20, 5 );
		add_action( 'wpmu_activate_user', array( $this, 'on_multisite_user_signup' ), 20, 3 );

		add_filter( 'add_signup_meta', array( $this, 'add_multisite_usermeta' ) );
	}

	/**
	* Add hidden checkbox to 2nd MultiSite registration form
	*/
	public function add_multisite_hidden_checkbox() {
		$value = $this->checkbox_was_checked() ? 1 : 0;
		?><input type="hidden" name="_mc4wp_subscribe" value="<?php echo $value; ?>" /><?php
	}

	/**
	* Subscribe from Multisite blog sign-ups
	*
	* @param int $blog_id
	* @param int $user_id
	* @return boolean
	*/
	public function on_multisite_blog_signup( $blog_id, $user_id, $a, $b , $meta = null ) {
		// was sign-up checkbox checked?
		if ( ! isset( $meta['_mc4wp_subscribe'] ) || $meta['_mc4wp_subscribe'] !== 1 ) {
			return false;
		}

		return $this->subscribe_from_multisite( $user_id );
	}

	/**
	* Subscribe from Multisite user sign-ups
	*
	* @param int $user_id
	* @param string $password
	* @param array $meta
	* @return boolean
	*/
	public function on_multisite_user_signup( $user_id, $password = NULL, $meta = array() ) {
		// abandon if sign-up checkbox was not checked
		if ( ! isset( $meta['_mc4wp_subscribe'] ) || $meta['_mc4wp_subscribe'] !== 1 ) {
			return false;
		}

		return $this->subscribe_from_multisite( $user_id );
	}

	/**
	* Add user meta from Multisite sign-ups to store the checkbox value
	* 
	* @param array $meta
	* @return array
	*/
	public function add_multisite_usermeta( $meta = array() ) {
		$meta['_mc4wp_subscribe'] = $this->checkbox_was_checked() ? 1 : 0;
		return $meta;
	}

	/**
	* Subscribe from Multisite forms
	*
	* @param int $user_id
	*/
	public function subscribe_from_multisite( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! is_object( $user ) ) {
			return false;
		}

		$email = $user->user_email;
		$merge_vars = array(
			'NAME' => $user->first_name . ' ' . $user->last_name,
			'FNAME' => $user->first_name,
			'LNAME' => $user->last_name
		);

		return $this->subscribe( $email, $merge_vars, 'multisite_registration' );
	}

}