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

		if ( is_multisite() ) {

			/**
			 * Multisite signups are a two-stage process - the data is first added to
			 * the 'signups' table and then converted into an actual user during the
			 * activation process.
			 *
			 * To avoid all signups being subscribed to the MailChimp list until they
			 * have responded to the activation email, a value is stored in the signup
			 * usermeta data which is retrieved on activation and acted upon.
			 */
			add_filter( 'bp_signup_usermeta', array( $this, 'store_usermeta' ), 10, 1 );
			add_action( 'bp_core_activated_user', array( $this, 'subscribe_from_usermeta' ), 10, 3 );

		} else {
			add_action( 'bp_core_signup_user', array( $this, 'subscribe_from_form' ), 10, 4 );
		}

		/**
		 * There is one further issue to consider, which is that many BuddyPress
		 * installs have a user moderation plugin (e.g. BP Registration Options)
		 * installed. This is because email activation is not enough to ensure
		 * that user signups are not spammers. There should therefore be a way for
		 * plugins to delay the MailChimp signup process.
		 *
		 * A plugin can hook into the 'mc4wp_delay_subscription' filter to prevent
		 * subscriptions from taking place on activation:
		 *
		 * add_filter( 'mc4wp_delay_subscription', 'my_delay_function' );
		 * function my_delay_function( $user_id ) {
		 *     // store a flag in their usermeta, for example
		 *     return false;
		 * }
		 *
		 * The plugin would then then call:
		 *
		 * do_action( 'mc4wp_do_delayed_subscription', $user_id );
		 *
		 * to perform the subscription at a later point.
		 */
		add_action( 'mc4wp_do_delayed_subscription', array( $this, 'subscribe_buddypress_user' ), 10, 1 );

	}

	/**
	 * Subscribes from BuddyPress Registration Form.
	 *
	 * @param int $user_id
	 * @param string $user_login
	 * @param string $user_password
	 * @param string $user_email
	 * @return bool
	 */
	public function subscribe_from_form( $user_id, $user_login, $user_password, $user_email ) {

		if ( ! $this->triggered() ) {
			return false;
		}

		/**
		 * Allow other plugins to delay MailChimp subscription.
		 *
		 * @param bool False does not delay subscription (default)
		 * @param int $user_id The user ID to subscribe
		 * @return bool False does not delay subscription, otherwise enforces delay
		 */
		if ( false !== apply_filters( 'mc4wp_delay_subscription', false, $user_id ) ) {
			return;
		}

		$this->subscribe_buddypress_user( $user_id );
	}

	/**
	 * Stores subscription data from BuddyPress Registration Form.
	 *
	 * @param array $usermeta The existing usermeta
	 * @return array $usermeta The modified usermeta
	 */
	public function store_usermeta( $usermeta ) {

		// do not subscribe if not triggered
		if ( ! $this->triggered() ) {
			$usermeta['mc4wp_delayed_subscribe'] = 'n';
		} else {
			$usermeta['mc4wp_delayed_subscribe'] = 'y';
		}

		return $usermeta;
	}

	/**
	 * Subscribes from BuddyPress Activation.
	 *
	 * @param int $user_id The activated user ID
	 * @param string $key the activation key (not used)
	 * @param array $userdata An array containing the activated user data
	 */
	public function subscribe_from_usermeta( $user_id, $key, $userdata ) {

		if ( empty( $user_id ) ) {
			return false;
		}

		// get metadata
		$meta = ( isset( $userdata['meta'] ) ) ? $userdata['meta'] : array();

		// bail if usermeta key doesn't exist or user chose not to subscribe
		if ( ! isset( $meta['mc4wp_delayed_subscribe'] ) || $meta['mc4wp_delayed_subscribe'] == 'n' ) {
			return false;
		}

		/**
		 * Allow other plugins to delay MailChimp subscription.
		 *
		 * @param bool False does not delay subscription (default)
		 * @param int $user_id The user ID to subscribe
		 * @return bool False does not delay subscription, otherwise enforces delay
		 */
		if ( false !== apply_filters( 'mc4wp_delay_subscription', false, $user_id ) ) {
			return;
		}

		$this->subscribe_buddypress_user( $user_id );
	}

	/**
	 * Subscribes a user to MailChimp list(s).
	 *
	 * @param int $user_id The user ID to subscribe
	 */
	public function subscribe_buddypress_user( $user_id ) {

		$user = get_userdata( $user_id );

		// was a user found with the given ID?
		if ( ! $user instanceof WP_User ) {
			return false;
		}

		// gather email address and name from user
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