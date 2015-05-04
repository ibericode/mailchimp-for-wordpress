<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
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
	* @var MC4WP_API
	*/
	private $api;

	/**
	 * @var MC4WP_Lite The one and only true plugin instance
	 */
	private static $instance;

	/**
	 * @return MC4WP_Lite
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Instantiates the plugin
	 *
	 * @return bool
	 */
	public static function init() {

		if( self::$instance instanceof MC4WP_Lite ) {
			return false;
		}

		self::$instance = new MC4WP_Lite;
		return true;
	}


	/**
	* Constructor
	*/
	private function __construct() {

		// checkboxes
		$this->checkbox_manager = new MC4WP_Lite_Checkbox_Manager();

		// forms
		add_action( 'init', array( $this, 'init_form_listener' ) );
		add_action( 'init', array( $this, 'init_form_manager' ) );

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	/**
	 * Initialise the form listener
	 * @hooked `init`
	 */
	public function init_form_listener() {
		$listener = new MC4WP_Form_Listener();
		$listener->listen( $_REQUEST );
	}

	/**
	 * Initialise the form manager
	 * @hooked `template_redirect`
	 */
	public function init_form_manager() {
		$this->form_manager = new MC4WP_Lite_Form_Manager();
		$this->form_manager->init();
	}

	/**
	* @return MC4WP_Lite_Checkbox_Manager
	*/
	public function get_checkbox_manager() {
		return $this->checkbox_manager;
	}

	/**
	* @return MC4WP_Lite_Form_Manager
	*/
	public function get_form_manager() {
		return $this->form_manager;
	}

	/**
	* @return MC4WP_API
	*/
	public function get_api() {

		if( $this->api === null ) {
			$opts = mc4wp_get_options();
			$this->api = new MC4WP_API( $opts['general']['api_key'] );
		}

		return $this->api;
	}

	public function register_widget() {
		register_widget( 'MC4WP_Lite_Widget' );
	}

}
