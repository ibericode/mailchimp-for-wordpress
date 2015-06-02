<?php

class MC4WP {

	/**
	* @var MC4WP_Form_Manager
	*/
	public $form_manager;

	/**
	* @var MC4WP_Checkbox_Manager
	*/
	public $checkbox_manager;

	/**
	 * @var MC4WP_Integrations
	 */
	public $integrations;

	/**
	* @var MC4WP_API
	*/
	protected $api;

	/**
	 * @var MC4WP The one and only true plugin instance
	 */
	protected static $instance;

	/**
	 * @return MC4WP
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

		if( self::$instance instanceof MC4WP ) {
			return false;
		}

		self::$instance = new MC4WP;
		return true;
	}


	/**
	* Constructor
	*/
	protected function __construct() {

		$checkbox_opts = mc4wp_get_options( 'checkbox' );

		// checkboxes
		$this->checkbox_manager = new MC4WP_Checkbox_Manager( $checkbox_opts );
		$this->checkbox_manager->add_hooks();

		// load integrations
		$this->integrations = new MC4WP_Integrations( $checkbox_opts );
		$this->integrations->load();

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
		$form_listener = new MC4WP_Form_Listener();
		$form_listener->listen( $_REQUEST );
	}

	/**
	 * Initialise the form manager
	 * @hooked `template_redirect`
	 */
	public function init_form_manager() {
		$this->form_manager = new MC4WP_Form_Manager();
		$this->form_manager->init();
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
		register_widget( 'MC4WP_Widget' );
	}

}
