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
	protected $integrations = array();

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
	* Constructor
	*/
	private function __construct() {
		self::$instance = $this;
		$this->options = mc4wp_get_integration_options();
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
		return ! empty( $this->options[ $slug ]['enabled'] );
	}


	/**
	 * Add hooks
	 */
	public function initialize() {
		// loop through integrations
		// initialize the ones which are enabled
		foreach( $this->get_enabled_integrations() as $slug ) {
			$integration = $this->integration( $slug );
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
	public function integration( $slug ) {

		$integrations = $this->get_integrations();

		if( ! array_key_exists( $slug, $integrations ) ) {
			throw new Exception( sprintf( "No integration with slug %s has been registered.", $slug ) );
		}

		// find instance of integration
		if( isset( $this->instances[ $slug ] ) ) {
			return $this->instances[ $slug ];
		}

		// none found, create new instance
		$classname = $integrations[ $slug ];
		$options = mc4wp_get_integration_options( $slug );
		$this->instances[ $slug ] = $instance = new $classname( $options );

		return $instance;
	}

	/**
	 * @param      $slug
	 * @param      $class
	 * @param bool $always_enabled
	 */
	public function add_integration( $slug, $class, $always_enabled = false ) {
		$this->integrations[ $slug ] = $class;

		if( $always_enabled ) {
			$this->always_enabled_integrations[] = $slug;
		}
	}

	/**
	 * @return array
	 */
	public function get_integrations() {

		/**
		 * Allow for other plugins to register their own integration class.
		 * The given class should extend `MC4WP_Integration`
		 *
		 * Format: slug => resolvable classname
		 * Example: 'my-plugin' => 'My_Plugin_MC4WP_Integration'
		 */
		return (array) apply_filters( 'mc4wp_integrations', $this->integrations );
	}

	/**
	 * Get the integrations which are enabled
	 *
	 * - Some integrations are always enabled because they need manual work
	 * - Other integrations can be enabled in the settings page
	 *
	 * Filter: `mc4wp_enabled_integrations`
	 *
	 * @return array
	 */
	public function get_enabled_integrations() {
		$always_enabled = $this->always_enabled_integrations;
		$user_enabled = array_filter( array_keys( $this->get_integrations() ), array( $this, 'is_enabled' ) );
		$enabled_integrations = array_merge( $always_enabled, $user_enabled );
		$integrations = (array) apply_filters( 'mc4wp_enabled_integrations', $enabled_integrations );
		return $integrations;
	}


}