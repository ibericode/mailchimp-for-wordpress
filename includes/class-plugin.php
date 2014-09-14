<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite {

	/**
	* @var MC4WP_Lite_Form_Manager
	*/
	private $form_manager;

	/**
	* @var MC4WP_Lite_Checkbox_Manager
	*/
	private $checkbox_manager;

	/**
	* @var MC4WP_Lite_API
	*/
	private $api = null;

	/**
	* Constructor
	*/
	public function __construct() {

        spl_autoload_register( array( $this, 'autoload') );

		// checkbox
		$this->checkbox_manager = new MC4WP_Lite_Checkbox_Manager();

		// form
		$this->form_manager = new MC4WP_Lite_Form_Manager();

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

    /**
     * @return bool
     */
    public function autoload( $class ) {

        static $classes = null;

        if( $classes === null ) {

            $include_path = MC4WP_LITE_PLUGIN_DIR . 'includes/';

            $classes = array(
                'mc4wp_lite_api' => $include_path . 'class-api.php',
                'mc4wp_lite_checkbox_manager' => $include_path . 'class-checkbox-manager.php',
                'mc4wp_lite_form_manager' => $include_path . 'class-form-manager.php',
                'mc4wp_lite_widget' => $include_path . 'class-widget.php',
                'mc4wp_lite_form_request' => $include_path . 'class-form-request.php',
	            'mc4wp_mailchimp' => $include_path . 'class-mailchimp.php',

                // integrations
                'mc4wp_integration' => $include_path . 'integrations/class-integration.php',
                'mc4wp_bbpress_integration' => $include_path . 'integrations/class-bbpress.php',
                'mc4wp_buddypress_integration' => $include_path . 'integrations/class-buddypress.php',
                'mc4wp_cf7_integration' => $include_path . 'integrations/class-cf7.php',
                'mc4wp_events_manager_integration' => $include_path . 'integrations/class-events-manager.php',
                'mc4wp_comment_form_integration' => $include_path . 'integrations/class-comment-form.php',
                'mc4wp_general_integration' => $include_path . 'integrations/class-general.php',
                'mc4wp_multisite_integration' => $include_path . 'integrations/class-multisite.php',
                'mc4wp_registration_form_integration' => $include_path . 'integrations/class-registration-form.php',
            );
        }

        $class_name = strtolower( $class );

        if( isset( $classes[$class_name] ) ) {
            require_once $classes[$class_name];
            return true;
        }

        return false;


    }

	/**
	* @return MC4WP_Lite_Checkbox
	*/
	public function get_checkbox_manager() {
		return $this->checkbox_manager;
	}

	/**
	* @return MC4WP_Lite_Form
	*/
	public function get_form_manager() {
		return $this->form_manager;
	}

	/**
	* @return MC4WP_Lite_API
	*/
	public function get_api() {

		if( $this->api === null ) {
			$opts = mc4wp_get_options();
			$this->api = new MC4WP_Lite_API( $opts['general']['api_key'] );
		}
		
		return $this->api;
	}

	public function register_widget()
	{
		register_widget( 'MC4WP_Lite_Widget' );
	}

}
