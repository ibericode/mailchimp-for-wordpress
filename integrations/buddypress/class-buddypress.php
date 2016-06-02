<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_BuddyPress_Integration
 *
 * @ignore
 */
class MC4WP_BuddyPress_Integration extends MC4WP_User_Integration {

	/**
	 * @var string
	 */
	public $name = "BuddyPress";

	/**
	 * @var string
	 */
	public $description = "Subscribes users from BuddyPress registration forms.";


	/**
	 * Add hooks
	 */
	public function add_hooks() {

		if( ! $this->options['implicit'] ) {
			add_action( 'bp_before_registration_submit_buttons', array( $this, 'output_checkbox' ), 20 );
		}

		add_action( 'bp_core_signup_user', array( $this, 'subscribe_from_buddypress' ), 10, 4 );
	}

	/**
	 * Subscribes from BuddyPress Registration Form
	 * @param int $user_id
	 * @param string $user_login
	 * @param string $user_password
	 * @param string $user_email
	 * @return bool
	 */
	public function subscribe_from_buddypress( $user_id, $user_login, $user_password, $user_email ) {

		if ( ! $this->triggered() ) {
			return false;
		}

		$user = get_userdata( $user_id );

		// was a user found with the given ID?
		if ( ! $user instanceof WP_User ) {
			return false;
		}

		// gather emailadress and name from user who BuddyPress registered
		$data = $this->user_merge_vars( $user );

		return $this->subscribe( $data, $user_id );
	}
	/* End BuddyPress functions */

	/**
	 * @return bool
	 */
	public function is_installed() {
		return class_exists( 'BuddyPress' );
	}

}