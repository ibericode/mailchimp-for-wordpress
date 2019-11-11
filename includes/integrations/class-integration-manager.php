<?php

/**
 * Class MC4WP_Integration_Manager
 *
 * @ignore
 * @access private
 */
class MC4WP_Integration_Manager {


	/**
	 * @var MC4WP_Integration_Fixture[]
	 */
	protected $integrations = array();

	/**
	 * @var MC4WP_Integration_Tags
	 */
	protected $tags;

	/**
	* Constructor
	*/
	public function __construct() {
		$this->tags = new MC4WP_Integration_Tags();
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'after_setup_theme', array( $this, 'initialize' ) );

		$this->tags->add_hooks();
	}


	/**
	 * Add hooks
	 */
	public function initialize() {
		/*** @var MC4WP_Integration_Fixture $integration */
		$enabled_integrations = $this->get_enabled_integrations();

		foreach ( $enabled_integrations as $integration ) {
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
		if ( ! isset( $this->integrations[ $slug ] ) ) {
			throw new Exception( sprintf( 'No integration with slug %s has been registered.', $slug ) );
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
		$raw_options                 = $this->get_integration_options( $slug );
		$this->integrations[ $slug ] = new MC4WP_Integration_Fixture( $slug, $class, $enabled, $raw_options );
	}

	/**
	 * Deregister an integration class
	 *
	 * @param string $slug
	 */
	public function deregister_integration( $slug ) {
		if ( isset( $this->integrations[ $slug ] ) ) {
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
	 * @param MC4WP_Integration $integration
	 * @return bool
	 */
	public function is_installed( $integration ) {
		return $integration->is_installed();
	}

	/**
	 * Get the integrations which are enabled
	 *
	 * - Some integrations are always enabled because they need manual work
	 * - Other integrations can be enabled in the settings page
	 * - Only returns installed integrations
	 *
	 * @return array
	 */
	public function get_enabled_integrations() {
		// get all enabled integrations
		$enabled_integrations = array_filter( $this->integrations, array( $this, 'is_enabled' ) );

		// remove duplicate values, for whatever reason..
		$enabled_integrations = array_unique( $enabled_integrations );

		// filter out integrations which are not installed
		$installed_enabled_integrations = array_filter( $enabled_integrations, array( $this, 'is_installed' ) );

		return $installed_enabled_integrations;
	}

	/**
	 * Gets all integration options in a keyed array
	 *
	 * @return array
	 */
	private function load_options() {
		$options = (array) get_option( 'mc4wp_integrations', array() );

		/**
		 * Filters global integration options
		 *
		 * This array holds ALL integration settings
		 *
		 * @since 3.0
		 * @param array $options
		 * @ignore
		 */
		return (array) apply_filters( 'mc4wp_integration_options', $options );
	}

	/**
	 * Gets the raw options for an integration
	 *
	 * @param $slug
	 * @return array
	 */
	public function get_integration_options( $slug ) {
		static $options;
		if ( $options === null ) {
			$options = $this->load_options();
		}

		return isset( $options[ $slug ] ) ? $options[ $slug ] : array();
	}
}
