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
		}

		// fire action for other plugins to hook into
		do_action( 'mc4wp_upgrade_routine', $this->database_version, $this->code_version, $this->installing );

		// update code version
		update_option( 'mc4wp_version', MC4WP_VERSION );
	}

	/**
	 * Change option keys
	 *
	 * mc4wp_lite > mc4wp
	 * mc4wp_lite_checkbox > mc4wp_integrations
	 * mc4wp_lite_form > mc4wp_form
	 * mc4wp_checkbox > mc4wp_integrations
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