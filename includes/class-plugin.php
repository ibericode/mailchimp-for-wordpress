<?php

/**
 * Class MC4WP
 *
 * @api
 */
class MC4WP {

	/**
	 * @const string
	 */
	const VERSION = MC4WP_VERSION;

	/**
	 * @const string
	 */
	const DIR = MC4WP_PLUGIN_DIR;

	/**
	 * @const string
	 */
	const URL = MC4WP_PLUGIN_URL;

	/**
	 * @const string
	 */
	const FILE = MC4WP_PLUGIN_FILE;

	/**
	* @var MC4WP_Form_Manager
	*/
	public $form_manager;

	/**
	* @var MC4WP_Integration_Manager
	*/
	public $integration_manager;

	/**
	 * @var MC4WP The one and only true plugin instance
	 */
	private static $instance;

	/**
	 * @var array
	 */
	public $options = array();

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
		$this->integration_manager->add_hooks();
	}
}
