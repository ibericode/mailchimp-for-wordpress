<?php

/**
 * This class takes care of all form related functionality
 *
 * @class MC4WP_Form_Manager
 * @internal
*/
class MC4WP_Form_Manager {

	/**
	 * @var array
	 */
	private $request_data = array();

	/**
	 * @var MC4WP_Form_Manager
	 */
	private static $instance;

	/**
	 * @var MC4WP_Form_Output_Manager
	 */
	public $output_manager;

	/**
	 * @return MC4WP_Form_Manager
	 */
	public static function instance() {

		if( self::$instance instanceof self ) {
			return self::$instance;
		}

		return new self;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		self::$instance = $this;

		// store global `$_REQUEST` array locally, to prevent other plugins from messing with it (yes it happens....)
		// todo: fix this properly (move to more specific $_POST?)
		$this->request_data = $_REQUEST;
		$this->output_manager = new MC4WP_Form_Output_Manager();
	}

	/**
	 * Hook!
	 */
	public function add_hooks() {

		add_action( 'init', array( $this, 'initialize' ) );

		// forms
		add_action( 'template_redirect', array( $this, 'init_asset_manager' ) );
		add_action( 'template_redirect', array( 'MC4WP_Form_Previewer', 'init' ) );

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		$this->output_manager->add_hooks();
	}

	/**
	 * Initialize
	 */
	public function initialize() {
		$this->register_post_type();
		$this->init_form_listener();
	}


	/**
	 * Register post type "mc4wp-form"
	 */
	public function register_post_type() {

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

	public function init_asset_manager() {
		$assets = new MC4WP_Form_Asset_Manager( $this->output_manager );
		$assets->initialize();
	}

	/**
	 * Register our Form widget
	 */
	public function register_widget() {
		register_widget( 'MC4WP_Form_Widget' );
	}
}
