<?php

/**
 * Class MC4WP_Integration_Manager
 */
class MC4WP_Integration_Manager {

	/**
	 * @var array Array holding all integration instances
	 */
	protected $instances = array();

	/**
	 * @var
	 */
	public $integrations = array();

	/**
	 * @var array
	 */
	protected $always_enabled_integrations = array();

	/**
	 * @var MC4WP_Integration_Manager
	 */
	protected static $instance;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var array
	 */
	private $classmap = array();

	/**
	* Constructor
	*/
	private function __construct() {
		self::$instance = $this;
	}

	/**
	 * Singleton method
	 *
	 * @return MC4WP_Integration_Manager
	 */
	public static function instance() {

		if( self::$instance instanceof self ) {
			return self::$instance;
		}

		return new self;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'after_setup_theme', array( $this, 'initialize' ) );
	}

	/**
	 * Checks whether a certain integration is enabled (in the settings)
	 *
	 * This is decoupled from the integration class itself as checking an array is way "cheaper" than instantiating an object
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function is_enabled( $slug ) {
		$options = $this->get_options( $slug );
		return ! empty( $options['enabled'] );
	}


	/**
	 * Add hooks
	 */
	public function initialize() {
		// loop through integrations
		// initialize the ones which are enabled
		foreach( $this->get_enabled_integrations() as $slug ) {
			$integration = $this->get_instance( $slug );
			$integration->initialize();
		}
	}


	/**
	 * Get an integration instance
	 *
	 * @param string $slug
	 * @return MC4WP_Integration
	 * @throws Exception
	 */
	public function get_instance( $slug ) {

		if( ! array_key_exists( $slug, $this->classmap ) ) {
			throw new Exception( sprintf( "No integration with slug %s has been registered.", $slug ) );
		}

		// find instance of integration
		if( isset( $this->instances[ $slug ] ) ) {
			return $this->instances[ $slug ];
		}

		// none found, create new instance
		$classname = $this->classmap[ $slug ];
		$options = $this->get_options( $slug );
		$this->instances[ $slug ] = $instance = new $classname( $slug, $options );

		return $instance;
	}

	/**
	 * Register a new integration class
	 *
	 * @param string $slug
	 * @param string $class
	 * @param bool $always_enabled
	 */
	public function register_integration( $slug, $class, $always_enabled = false ) {
		$this->integrations[] = $slug;
		$this->classmap[ $slug ] = $class;

		if( $always_enabled ) {
			$this->always_enabled_integrations[] = $slug;
		}
	}

	/**
	 * Deregister an integration class
	 *
	 * @param string $slug
	 */
	public function deregister_integration( $slug ) {

		$index = array_search( $slug, $this->integrations );
		if( $index ) {
			unset( $this->integrations[ $index ] );
		}

		if( array_key_exists( $slug, $this->classmap ) ) {
			unset( $this->classmap[ $slug ] );
		}

		$index = array_search( $slug, $this->always_enabled_integrations, true );
		if( $index ) {
			unset( $this->always_enabled_integrations[ $index ] );
		}
	}

	/**
	 * Get the integrations which are enabled
	 *
	 * - Some integrations are always enabled because they need manual work
	 * - Other integrations can be enabled in the settings page
	 *
	 * @return array
	 */
	public function get_enabled_integrations() {
		$always_enabled = $this->always_enabled_integrations;
		$user_enabled = array_filter( $this->integrations, array( $this, 'is_enabled' ) );
		$enabled_integrations = array_merge( $always_enabled, $user_enabled );
		return $enabled_integrations;
	}

	/**
	 * @param string $slug
	 *
	 * @return array
	 */
	public function get_options( $slug = '' ) {
		$options = (array) get_option( 'mc4wp_integrations', array() );
		if( $slug === '' ) {
			return (array) apply_filters( 'mc4wp_integration_options', $options );
		}

		$integration_options = require MC4WP_PLUGIN_DIR . 'config/default-integration-options.php';
		if( isset( $options[ $slug ] ) && is_array( $options[ $slug] ) ) {
			$integration_options = array_merge( $integration_options, $options[ $slug ] );
		}

		return (array) apply_filters( 'mc4wp_' . $slug . '_integration_options', $integration_options );
	}

}