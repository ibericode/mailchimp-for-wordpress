<?php

class MC4WP {

	/**
	* @var MC4WP_Form_Manager
	*/
	public $form_manager;

	/**
	* @var MC4WP_Integration_Manager
	*/
	public $integration_manager;

	/**
	* @var MC4WP_API
	*/
	private $api;

	/**
	 * @var MC4WP The one and only true plugin instance
	 */
	private static $instance;

	/**
	 * @return MC4WP
	 */
	public static function instance() {

		if( ! self::$instance ) {
			self::$instance = new MC4WP;
		}

		return self::$instance;
	}


	/**
	* Constructor
	*/
	private function __construct() {

		// forms
		$this->form_manager = new MC4WP_Form_Manager();
		$this->form_manager->add_hooks();

		// checkboxes
		$this->integration_manager = new MC4WP_Integration_Manager();
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {

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


}
