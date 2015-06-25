<?php

class MC4WP_Upgrade_Routine {

	/**
	 * @var int
	 */
	protected $database_version = 0;

	/**
	 * @var
	 */
	protected $code_version = 0;

	/**
	 * @param string $code_version The version we're upgrading to
	 * @param string $database_version The version the current database data is at
	 */
	public function __construct( $code_version, $database_version ) {
		$this->database_version = $database_version;
		$this->code_version = $code_version;
		$this->installing = ( $database_version === 0 );
	}

	/**
	 * Run the various upgrade routines, all the way up to the latest version
	 */
	public function run() {
		define( 'MC4WP_DOING_UPGRADE', true );

		// upgrade to 2.3
		if( ! $this->installing && version_compare( $this->database_version, '2.3', '<' ) ) {
			$this->change_success_message_key();
		}

		// upgrade to 3.0
		if( ! $this->installing && version_compare( $this->database_version, '3.0', '<' ) ) {
			$this->change_option_keys();
			$this->change_integration_option_structure();
		}

		// fire action for other plugins to hook into
		do_action( 'mc4wp_upgrade_routine', $this->database_version, $this->code_version, $this->installing );

		// update code version
		update_option( 'mc4wp_version', MC4WP_VERSION );
	}

	/**
	 * Move certain integration specific settings to the 'custom_settings' array
	 * @since 3.0
	 */
	protected function change_integration_option_structure() {
		$options = get_option( 'mc4wp_integrations' );

		if( ! isset( $options['custom_settings'] ) ) {
			$options['custom_settings'] = array();
		}

		$map = array(
			'comment_form' => 'comment_form',
			'registration_form' => 'registration_form',
			'buddypress_form' => 'buddypress',
			'multisite_form' => 'multisite',
			'bbpress_forms' => 'bbpress',
			'woocommerce_checkout' => 'woocommerce',
			'edd_checkout' => 'easy_digital_downloads'
		);

		foreach( $map as $old_key => $new_key ) {

			// make sure custom settings exist for this integration
			if( ! isset( $options['custom_settings'][ $new_key ] ) ) {
				$options['custom_settings'] = array();
			}

			// update "show_at_xxx" value
			if( isset( $options['show_at_' . $old_key ] ) ) {
				$options['custom_settings'][$new_key]['enabled'] = $options['show_at_' . $old_key];
				unset( $options['show_at_' . $old_key] );
			}

			// update custom texts
			if( isset( $options['text_' . $old_key . '_label'] ) ) {
				$options['custom_settings'][$new_key]['label'] = $options['text_' . $old_key . '_label' ];
				unset( $options['text_' . $old_key . '_label' ] );
			}

		}

		update_option( 'mc4wp_integrations', $options );
	}

	/**
	 * Change option keys
	 *
	 * mc4wp_lite > mc4wp
	 * mc4wp_lite_checkbox > mc4wp_integrations
	 * mc4wp_lite_form > mc4wp_form
	 * mc4wp_checkbox > mc4wp_integrations
	 *
	 * @since 3.0
	 */
	protected function change_option_keys() {
		$keys = array(
			'mc4wp_lite' => 'mc4wp',
			'mc4wp_lite_checkbox' => 'mc4wp_integrations',
			'mc4wp_lite_form' => 'mc4wp_form',
			'mc4wp_checkbox' => 'mc4wp_integrations'
		);

		foreach( $keys as $old_key => $new_key ) {
			$old_option = get_option( $old_key, false );
			$new_option = get_option( $new_key, false );

			// only transfer if new option is not set
			if( is_array( $old_option ) && ! $new_option ) {
				update_option( $new_key, $old_option );
				delete_option( $old_key );
			}
		}
	}

	/**
	 * Change key of option name holding the "subscribed" message
	 * @since 2.3
	 */
	protected function change_success_message_key() {
		$options = get_option( 'mc4wp_lite_form' );
		if( ! empty( $options['text_success'] ) && empty( $options['text_subscribed'] ) ) {
			$options['text_subscribed'] = $options['text_success'];
			unset( $options['text_success'] );
		}

		update_option( 'mc4wp_lite_form',$options );
	}








}