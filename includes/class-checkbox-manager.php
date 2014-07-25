<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite_Checkbox_Manager
{
	/**
	 * @var array Array holding all integration instances
	 */
	public $integrations = array();

	/**
	* Constructor
	*/
	public function __construct()
	{
        $opts = mc4wp_get_options( 'checkbox' );

        // load checkbox css if necessary
        add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'load_stylesheet' ) );

        // Load WP Comment Form Integration
        if ( $opts['show_at_comment_form'] ) {
            $this->integrations['comment_form'] = new MC4WP_Comment_Form_Integration();
        }

        // Load WordPress Registration Form Integration
        if ( $opts['show_at_registration_form'] ) {
            $this->integrations['registration_form'] = new MC4WP_Registration_Form_Integration();
        }

        // Load BuddyPress Integration
        if ( $opts['show_at_buddypress_form'] ) {
            $this->integrations['buddypress_form'] = new MC4WP_BuddyPress_Integration();
        }

        // Load MultiSite Integration
        if ( $opts['show_at_multisite_form'] ) {
            $this->integrations['multisite_form'] = new MC4WP_MultiSite_Integration();
        }

        // Load bbPress Integration
        if ( $opts['show_at_bbpress_forms'] ) {
            $this->integrations['bbpress_forms'] = new MC4WP_bbPress_Integration();
        }

        // Load CF7 Integration
        if( function_exists( 'wpcf7_add_shortcode' ) ) {
            $this->integrations['contact_form_7'] = new MC4WP_CF7_Integration();
        }

        // Always load General Integration
		$this->integrations['general'] = new MC4WP_General_Integration();
	}

	/**
	* Loads the checkbox stylesheet
	*/
	public function load_stylesheet( ) {

        $opts = mc4wp_get_options( 'checkbox' );

        if( $opts['css'] == false ) {
            return false;
        }

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'mailchimp-for-wp-checkbox', MC4WP_LITE_PLUGIN_URL . 'assets/css/checkbox' . $suffix . '.css', array(), MC4WP_LITE_VERSION, 'all' );
        return true;
	}

}