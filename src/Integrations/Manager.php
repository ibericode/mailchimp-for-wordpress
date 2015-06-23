<?php

/**
 * Class MC4WP_Integrations
 *
 * todo: Change old "show_at_xxx_" to new option structure
 * todo: Upgrade integration types in database log table
 * todo: Upgrade from custom text options
 */
class MC4WP_Integrations_Manager {

	/**
	 * @var array
	 */
	protected $integrations = array();

	/**
	 * @var array
	 */
	protected $registered_integrations = array(
		'comment_form' => 'Comment_Form',
		'registration_form' => 'Registration_Form',
		'buddypress'  => 'BuddyPress',
		'bbpress'  => 'bbPress',
		'woocommerce'  => 'WooCommerce',
		'easy_digital_downloads'  => 'EDD',
		'contact_form_7'  => 'CF7',
		'events_manager'  => 'Events_Manager',
		'multisite' => 'MultiSite',
		'custom'  => 'Custom',

	);

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var MC4WP_Integrations_Assets
	 */
	public $asset_manager;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = $this->load_options();
	}

	/**
	 * @param $name
	 *
	 * @return null
	 */
	public function __get( $name ) {

		if( isset( $this->integrations[ $name ] ) ) {
			return $this->integrations[ $name ];
		}

		if( isset( $this->registered_integrations[ $name ] ) ) {
			$classname = 'MC4WP_' . $this->registered_integrations[ $name ] .'_Integration';
			$this->integrations[ $name ] = new $classname( $this->options );
			return $this->integrations[ $name ];
		}

		return null;
	}

	/**
	 *
	 */
	public function init() {
		$this->add_hooks();
		$this->init_integrations();
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// load asset manager on "template_redirect"
		add_action( 'template_redirect', array( $this, 'init_assets' ) );
	}

	/**
	 * Initialise Asset Manager
	 *
	 * @internal
	 * @hooked `template_redirect`
	 */
	public function init_assets() {
		$this->asset_manager = new MC4WP_Integrations_Assets( $this->options );
		$this->asset_manager->add_hooks();
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public function is_enabled( $type ) {
		return ( isset( $this->options[ 'custom_settings'][ $type ]['enabled'] ) && $this->options[ 'custom_settings'][ $type ]['enabled'] );
	}

	/**
	 * Init the various integrations
	 */
	public function init_integrations() {

		// Load WP Comment Form Integration
		if( $this->is_enabled( 'comment_form' ) ) {
			$this->comment_form->init();
		}

		// Load WordPress Registration Form Integration
		if( $this->is_enabled( 'registration_form' ) ) {
			$this->registration_form->init();
		}

		// Load BuddyPress Integration
		if( $this->is_enabled( 'buddypress' ) ) {
			$this->buddypress->init();
		}

		// Load MultiSite Integration
		if( $this->is_enabled( 'multisite' ) ) {
			$this->multisite->init();
		}

		// Load bbPress Integration
		if( $this->is_enabled( 'bbpress' ) ) {
			$this->bbpress->init();
		}

		// Load CF7 Integration
		if( function_exists( 'wpcf7_add_shortcode' ) ) {
			$this->contact_form_7->init();
		}

		// Load Events Manager integration
		if( defined( 'EM_VERSION' ) ) {
			$this->events_manager->init();
		}

		// Load WooCommerce Integration
		if( $this->is_enabled( 'woocommerce' ) ) {
			$this->woocommerce->init();
		}

		// Load EDD Integration
		if( $this->is_enabled( 'easy_digital_downloads' ) ) {
			$this->easy_digital_downloads->init();
		}

		// load General Integration on POST requests
		if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$this->custom->init();
		}
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = (array) get_option( 'mc4wp_integrations', array() );
		$defaults = include MC4WP_PLUGIN_DIR . '/config/default-options.php';
		$options = array_merge( $defaults['integrations'], $options );
		return $options;
	}

	/**
	 * @param $type
	 * @param bool $inherit
	 *
	 * @return array
	 */
	public function get_integration_options( $type, $inherit = true ) {

		if( ! $inherit ) {
			return $this->{$type}->custom_options;
		}

		return $this->{$type}->options;
	}

	/**
	 * Returns available checkbox integrations
	 *
	 * @return array
	 */
	public function get_available_integrations() {
		static $checkbox_plugins;

		if( is_array( $checkbox_plugins ) ) {
			return $checkbox_plugins;
		}

		$checkbox_plugins = array(
			'comment_form'          => __( 'Comment form', 'mailchimp-for-wp' ),
			'registration_form'     => __( 'Registration form', 'mailchimp-for-wp' )
		);

		if( is_multisite() ) {
			$checkbox_plugins['multisite'] = __( 'MultiSite', 'mailchimp-for-wp' );
		}

		if( class_exists( 'BuddyPress' ) ) {
			$checkbox_plugins['buddypress'] = __( 'BuddyPress', 'mailchimp-for-wp' );
		}

		if( class_exists( 'bbPress' ) ) {
			$checkbox_plugins['bbpress'] = 'bbPress';
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$checkbox_plugins['woocommerce'] = 'WooCommerce';
		}

		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			$checkbox_plugins['easy_digital_downloads'] = 'Easy Digital Downloads';
		}

		return $checkbox_plugins;
	}

}