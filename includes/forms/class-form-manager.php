<?php

/**
* This class takes care of all form related functionality
*/
class MC4WP_Form_Manager {

	/**
	 * @var array
	 */
	private $request_data = array();

	/**
	 * @var MC4WP_Form_Listener
	 */
	private $listener;

	/**
	 * @var MC4WP_Form_Asset_Manager;
	 */
	private $asset_manager;

	/**
	 * Constructor
	 */
	public function __construct() {
		// store global `$_REQUEST` array locally, to prevent other plugins from messing with it (yes it happens....)
		// todo: fix this properly (move to more specific $_POST?)
		$this->request_data = $_REQUEST;
	}

	/**
	 * Hook!
	 */
	public function add_hooks() {
		// forms
		add_action( 'init', array( $this, 'init_form_listener' ) );
		add_action( 'init', array( $this, 'init_form_asset_manager' ) );

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	/**
	 * Initialise the form listener
	 *
	 * @hooked `init`
	 */
	public function init_form_listener() {
		$listener = new MC4WP_Form_Listener();
		$listener->listen( $this->request_data );
	}

	/**
	 * Initialise the form asset manager
	 *
	 * @hooked `init`
	 */
	public function init_form_asset_manager() {
		$this->asset_manager = new MC4WP_Form_Asset_Manager();
		$this->asset_manager->init();
	}

	/**
	 * Register our Form widget
	 */
	public function register_widget() {
		register_widget( 'MC4WP_Form_Widget' );
	}

	/**
	 * @param $args
	 *
	 * @return string
	 */
	public function output_form( $args ) {
		return $this->asset_manager->output_form( $args );
	}
}
