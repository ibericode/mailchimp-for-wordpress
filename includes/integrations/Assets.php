<?php

/**
 * Takes care of all the sign-up checkboxes
 */
class MC4WP_Integration_Assets {

	/**
	 * @var array Array of checkbox options
	 */
	private $options = array();


	/**
	* Constructor
	 *
	 * @param array $options
	*/
	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// load checkbox css if necessary
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'load_stylesheet' ) );
	}

	/**
	* Loads the checkbox stylesheet
	*/
	public function load_stylesheet( ) {

		if( $this->options['css'] == false ) {
			return false;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'mailchimp-for-wp-checkbox', MC4WP_PLUGIN_URL . 'assets/css/checkbox-reset' . $suffix . '.css', array(), MC4WP_VERSION, 'all' );
		return true;
	}

}