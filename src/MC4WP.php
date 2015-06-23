<?php

class MC4WP {

	/**
	* @var MC4WP_Forms_Manager
	*/
	public $forms;

	/**
	 * @var MC4WP_Integrations_Manager
	 */
	public $integrations;

	/**
	* @var MC4WP_API
	*/
	public $api;

	/**
	 * @var array
	 */
	public $options;

	/**
	* Constructor
	*/
	public function __construct() {

		$this->options = $this->load_options();

		// load integrations
		$this->integrations = new MC4WP_Integrations_Manager();

		// load forms
		$this->forms = new MC4WP_Forms_Manager();
	}

	/**
	 * Initialise plugin and sub-functionality
	 */
	public function init() {
		$this->add_hooks();

		$this->integrations->init();
		$this->forms->init();
	}

	/**
	 * Add hooks
	 */
	protected function add_hooks() {
		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
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

	/**
	 * Register widget
	 */
	public function register_widget() {
		register_widget( 'MC4WP_Widget' );
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = (array) get_option( 'mc4wp', array() );
		$defaults = include MC4WP_PLUGIN_DIR . '/config/default-options.php';
		$options = array_merge( $defaults['general'], $options );
		return $options;
	}

}
