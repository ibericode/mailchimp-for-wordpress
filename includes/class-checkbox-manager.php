<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Takes care of all the sign-up checkboxes
 */
class MC4WP_Lite_Checkbox_Manager
{
	/**
	 * @var array Array holding all integration instances
	 */
	public $integrations = array();

	/**
	 * @var array Array of checkbox options
	 */
	private $options;

	/**
	* Constructor
	*/
	public function __construct()
	{
		$this->options = mc4wp_get_options( 'checkbox' );

		// load checkbox css if necessary
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'load_stylesheet' ) );

		// Load WP Comment Form Integration
		if ( $this->options['show_at_comment_form'] ) {
			$this->integrations['comment_form'] = new MC4WP_Comment_Form_Integration();
		}

		// Load WordPress Registration Form Integration
		if ( $this->options['show_at_registration_form'] ) {
			$this->integrations['registration_form'] = new MC4WP_Registration_Form_Integration();
		}

		// Load BuddyPress Integration
		if ( $this->options['show_at_buddypress_form'] ) {
			$this->integrations['buddypress_form'] = new MC4WP_BuddyPress_Integration();
		}

		// Load MultiSite Integration
		if ( $this->options['show_at_multisite_form'] ) {
			$this->integrations['multisite_form'] = new MC4WP_MultiSite_Integration();
		}

		// Load bbPress Integration
		if ( $this->options['show_at_bbpress_forms'] ) {
			$this->integrations['bbpress_forms'] = new MC4WP_bbPress_Integration();
		}

		// Load CF7 Integration
		if( function_exists( 'wpcf7_add_shortcode' ) ) {
			$this->integrations['contact_form_7'] = new MC4WP_CF7_Integration();
		}

		// Load Events Manager integration
		if( defined( 'EM_VERSION' ) ) {
			$this->integrations['events_manager'] = new MC4WP_Events_Manager_Integration();
		}

		// Load WooCommerce Integration
		if ( $this->options['show_at_woocommerce_checkout'] ) {
			$this->integrations['woocommerce'] = new MC4WP_WooCommerce_Integration();
		}

		// Load EDD Integration
		if ( $this->options['show_at_edd_checkout'] ) {
			$this->integrations['easy_digital_downloads'] = new MC4WP_EDD_Integration();
		}

		// load General Integration on POST requests
		if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$this->integrations['general'] = new MC4WP_General_Integration();
		}

	}

	/**
	* Loads the checkbox stylesheet
	*/
	public function load_stylesheet( ) {

		if( $this->options['css'] == false ) {
			return false;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'mailchimp-for-wp-checkbox', MC4WP_LITE_PLUGIN_URL . 'assets/css/checkbox' . $suffix . '.css', array(), MC4WP_LITE_VERSION, 'all' );
		return true;
	}

}