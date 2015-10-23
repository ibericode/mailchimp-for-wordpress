<?php

/**
 * Class MC4WP_Integration_Manager
 * @internal
 */
class MC4WP_Integration_Manager {

	/**
	 * @var MC4WP_Integration_Manager
	 */
	protected static $instance;

	/**
	 * @var MC4WP_Integration_Fixture[]
	 */
	protected $integrations = array();

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	* Constructor
	*/
	private function __construct() {
		self::$instance = $this;
		$this->options = $this->get_options();
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
	 * Add hooks
	 */
	public function initialize() {
		/*** @var MC4WP_Integration_Fixture $integration */
		foreach( $this->get_enabled_integrations() as $integration ) {
			$integration->load()->initialize();
		}
	}

	/**
	 * Get an integration instance
	 *
	 * @return MC4WP_Integration_Fixture[]
	 * @throws Exception
	 */
	public function get_all() {
		return $this->integrations;
	}


	/**
	 * Get an integration instance
	 *
	 * @param string $slug
	 * @return MC4WP_Integration
	 * @throws Exception
	 */
	public function get( $slug ) {

		if( ! isset( $this->integrations[ $slug ] ) ) {
			throw new Exception( sprintf( "No integration with slug %s has been registered.", $slug ) );
		}

		return $this->integrations[ $slug ]->load();
	}

	/**
	 * Register a new integration class
	 *
	 * @param string $slug
	 * @param string $class
	 * @param bool $enabled
	 */
	public function register_integration( $slug, $class, $enabled = false ) {
		$raw_options = $this->get_integration_options( $slug );
		$this->integrations[ $slug ] = new MC4WP_Integration_Fixture( $slug, $class, $enabled, $raw_options );
	}

	/**
	 * Deregister an integration class
	 *
	 * @param string $slug
	 */
	public function deregister_integration( $slug ) {
		if( isset( $this->integrations[ $slug ] ) ) {
			unset( $this->integrations[ $slug ] );
		}
	}

	/**
	 * Checks whether a certain integration is enabled (in the settings)
	 *
	 * This is decoupled from the integration class itself as checking an array is way "cheaper" than instantiating an object
	 *
	 * @param MC4WP_Integration_Fixture $integration
	 *
	 * @return bool
	 */
	public function is_enabled( MC4WP_Integration_Fixture $integration ) {
		return $integration->enabled;
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
		return array_filter( $this->integrations, array( $this, 'is_enabled' ) );
	}

	/**
	 * @return array
	 */
	public function get_options() {
		$options = (array) get_option( 'mc4wp_integrations', array() );
		return (array) apply_filters( 'mc4wp_integration_options', $options );
	}

	/**
	 * Get the raw options for an integration
	 *
	 * @param $slug
	 * @return array
	 */
	public function get_integration_options( $slug ) {
		return isset( $this->options[ $slug ] ) ? $this->options[ $slug ] : array();
	}

}