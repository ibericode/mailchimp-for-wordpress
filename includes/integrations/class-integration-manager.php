<?php

/**
 * Class MC4WP_Integration_Manager
 */
class MC4WP_Integration_Manager {

	/**
	 * @var array Array holding all integration instances
	 */
	private $integrations = array();


	/**
	 * @var array
	 */
	public $default_integrations = array(
		'wp-comment-form' => 'Comment_Form',
		'wp-registration-form' => 'Registration_Form',
		'buddypress'  => 'BuddyPress',
		'woocommerce'  => 'WooCommerce',
		'easy-digital-downloads'  => 'Easy_Digital_Downloads',
		'contact-form-7'  => 'Contact_Form_7',
		'events-manager'  => 'Events_Manager',
		'custom'  => 'Custom',
	);

	/**
	 * @var array
	 */
	public $registered_integrations = array();

	/**
	 * @var MC4WP_Integration_Manager
	 */
	public static $instance;

	/**
	 * @var array
	 */
	protected $options;

	/**
	* Constructor
	*/
	public function __construct() {
		$this->options = mc4wp_get_integration_options();
		$this->registered_integrations = $this->get_registered_integrations();
	}

	/**
	 * @return array
	 */
	protected function get_registered_integrations() {
		$integrations = array();

		// convert classnames of default integrations
		foreach( $this->default_integrations as $key => $classname ) {
			$integrations[ $key ] = sprintf( 'MC4WP_%s_Integration', $classname );
		}

		/**
		 * Allow for other plugins to register their own integration class.
		 * The given class should extend `MC4WP_Integration`
		 *
		 * Format: slug => resolvable classname
		 * Example: 'my-plugin' => 'My_Plugin_MC4WP_Integration'
		 */
		return (array) apply_filters( 'mc4wp_integrations', $integrations );
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
		return ( ! empty( $this->options[ $slug ]['enabled'] ) );
	}

	/**
	 * @return MC4WP_Integration_Manager
	 */
	public static function instance() {

		if( self::$instance instanceof self ) {
			return self::$instance;
		}

		self::$instance = new self;
		return self::$instance;
	}


	/**
	 * Add hooks
	 */
	public function initialize() {
		// loop through integrations
		// initialize the ones which are enabled
		foreach( $this->registered_integrations as $slug => $class ) {
			if( $this->is_enabled( $slug ) ) {
				$integration = $this->integration( $slug );
				$integration->initialize();
			}
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

		if( ! array_key_exists( $slug, $this->registered_integrations ) ) {
			throw new Exception( sprintf( "No integration with slug %s has been registered.", $slug ) );
		}

		// find instance of integration
		if( isset( $this->integrations[ $slug ] ) ) {
			return $this->integrations[ $slug ];
		}

		// create new instance
		$classname = $this->registered_integrations[ $slug ];
		$options = mc4wp_get_integration_options( $slug );
		$this->integrations[ $slug ] = $instance = new $classname( $options );

		return $instance;
	}
}