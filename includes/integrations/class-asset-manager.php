<?php

class MC4WP_Integrations_Asset_Manager {

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}


	/**
	 *
	 */
	public function add_hooks() {
		// load checkbox css if necessary
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * @return bool
	 */
	public function load_assets() {
		if( $this->options['css'] == false ) {
			return false;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'mailchimp-for-wp-checkbox', MC4WP_PLUGIN_URL . 'assets/css/checkbox' . $suffix . '.css', array(), MC4WP_VERSION, 'all' );
		return true;
	}
}