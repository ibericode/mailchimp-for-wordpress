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
	public function initialize() {

		add_action( 'init', array( $this, 'register_form_type' ) );

		// forms
		add_action( 'init', array( $this, 'init_form_listener' ) );
		add_action( 'init', array( $this, 'init_form_asset_manager' ) );
		add_action( 'template_redirect', array( 'MC4WP_Form_Previewer', 'init' ) );

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	/**
	 * Register post type "mc4wp-form"
	 */
	public function register_form_type() {

		// register post type
		register_post_type( 'mc4wp-form', array(
				'labels' => array(
					'name' => 'MailChimp Sign-up Forms',
					'singular_name' => 'Sign-up Form',
					'add_new_item' => 'Add New Form',
					'edit_item' => 'Edit Form',
					'new_item' => 'New Form',
					'all_items' => 'All Forms',
					'view_item' => null
				),
				'public' => false
			)
		);
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
		$this->asset_manager = new MC4WP_Form_Asset_Manager( );
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
