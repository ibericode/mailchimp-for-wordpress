<?php

/**
 * Class MC4WP_Admin
 *
 * @ignore
 * @access private
 */
class MC4WP_Admin {

	/**
	 * @var string The relative path to the main plugin file from the plugins dir
	 */
	protected $plugin_file;

	/**
	 * @var MC4WP_MailChimp
	 */
	protected $mailchimp;

	/**
	 * @var MC4WP_Admin_Messages
	 */
	protected $messages;

	/**
	 * @var MC4WP_Admin_Ads
	 */
	protected $ads;

	/**
	 * Constructor
	 *
	 * @param MC4WP_Admin_Messages $messages
	 * @param MC4WP_MailChimp      $mailchimp
	 */
	public function __construct( MC4WP_Admin_Messages $messages, MC4WP_MailChimp $mailchimp ) {
		$this->mailchimp = $mailchimp;
		$this->messages = $messages;
		$this->plugin_file = plugin_basename( MC4WP_PLUGIN_FILE );
		$this->ads = new MC4WP_Admin_Ads();
		$this->load_translations();
	}

	/**
	 * Registers all hooks
	 */
	public function add_hooks() {

		// Actions used globally throughout WP Admin
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'current_screen', array( $this, 'customize_admin_texts' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
		add_action( 'mc4wp_admin_empty_lists_cache', array( $this, 'renew_lists_cache' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$this->ads->add_hooks();
		$this->messages->add_hooks();
	}

	/**
	 * Initializes various stuff used in WP Admin
	 *
	 * - Registers settings
	 */
	public function initialize() {

		// register settings
		register_setting( 'mc4wp_settings', 'mc4wp', array( $this, 'save_general_settings' ) );

		// Load upgrader
		$this->init_upgrade_routines();

		// listen for custom actions
		$this->listen_for_actions();
	}


	/**
	 * Listen for `_mc4wp_action` requests
	 */
	public function listen_for_actions() {

		// listen for any action (if user is authorised)
		if( ! current_user_can( 'manage_options' ) || ! isset( $_REQUEST['_mc4wp_action'] ) ) {
			return false;
		}

		$action = (string) $_REQUEST['_mc4wp_action'];

		/**
		 * Allows you to hook into requests containing `_mc4wp_action` => action name.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the action name.
		 *
		 * By the time this hook is fired, the user is already authorized. After processing all the registered hooks,
		 * the request is redirected back to the referring URL.
		 *
		 * @since 3.0
		 */
		do_action( 'mc4wp_admin_' . $action );

		// redirect back to where we came from
		$redirect_url = remove_query_arg( '_mc4wp_action' );
		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Register dashboard widgets
	 */
	public function register_dashboard_widgets() {

		if( ! $this->is_user_authorized() ) {
			return false;
		}

		/**
		 * Setup dashboard widget, users are authorized by now.
		 *
		 * Use this hook to register your own dashboard widgets for users with the required capability.
		 *
		 * @since 3.0
		 */
		do_action( 'mc4wp_dashboard_setup' );

		return true;
	}

	/**
	 * Upgrade routine
	 */
	private function init_upgrade_routines() {

		// upgrade routine for upgrade routine....
		$previous_version = get_option( 'mc4wp_lite_version', 0 );
		if( $previous_version ) {
			delete_option( 'mc4wp_lite_version' );
			update_option( 'mc4wp_version', $previous_version );
		}

		// Only run if db option is at older version than code constant
		$previous_version = get_option( 'mc4wp_version', 0 );

		// This ! check means we're not running when installing the plugin
		if( ! $previous_version || version_compare( MC4WP_VERSION, $previous_version, '<=' ) ) {
			return false;
		}

		define( 'MC4WP_DOING_UPGRADE', true );
		$upgrade_routines = new MC4WP_Upgrade_Routines( $previous_version, MC4WP_VERSION, dirname( __FILE__ ) . '/migrations' );
		$upgrade_routines->run();
		update_option( 'mc4wp_version', MC4WP_VERSION );
	}

	/**
	 * Renew MailChimp lists cache
	 */
	public function renew_lists_cache() {
		$this->mailchimp->empty_cache();

		// try getting new lists to fill cache again
		$lists = $this->mailchimp->get_lists();
		if( ! empty( $lists ) ) {
			$this->messages->flash( __( 'Success! The cached configuration for your MailChimp lists has been renewed.', 'mailchimp-for-wp' ) );
		}
	}

	/**
	 * Load the plugin translations
	 */
	private function load_translations() {
		// load the plugin text domain
		load_plugin_textdomain( 'mailchimp-for-wp', false, dirname( $this->plugin_file ) . '/languages' );
	}

	/**
	 * Customize texts throughout WP Admin
	 */
	public function customize_admin_texts() {
		$texts = new MC4WP_Admin_Texts( $this->plugin_file );
		$texts->add_hooks();
	}




	/**
	 * Validates the General settings
	 * @param array $settings
	 * @return array
	 */
	public function save_general_settings( array $settings ) {

		$current = mc4wp_get_options();

		// Toggle usage tracking
		if( isset( $settings['allow_usage_tracking'] ) ) {
			MC4WP_Usage_Tracking::instance()->toggle( (bool) $settings['allow_usage_tracking'] );
		}

		// Sanitize API key & empty cache when API key changed
		if( isset( $settings['api_key'] ) ) {

			$settings['api_key'] = sanitize_text_field( $settings['api_key'] );

			if ( $settings['api_key'] !== $current['api_key'] ) {
				$this->mailchimp->empty_cache();
			}
		}

		return $settings;
	}

	/**
	 * Load scripts and stylesheet on MailChimp for WP Admin pages
	 *
	 * @return bool
	*/
	public function enqueue_assets() {

		global $wp_scripts;

		$prefix = 'mailchimp-for-wp';

		// only load asset files on the MailChimp for WordPress settings pages
		if( empty( $_GET['page'] ) || strpos( $_GET['page'], $prefix ) !== 0 ) {
			return false;
		}

		$page = ltrim( substr( $_GET['page'], strlen( $prefix ) ), '-' );
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// css
		wp_register_style( 'mc4wp-admin', MC4WP_PLUGIN_URL . 'assets/css/admin-styles' . $suffix . '.css', array(), MC4WP_VERSION );
		wp_enqueue_style( 'mc4wp-admin' );


		// js
		wp_register_script( 'es5-shim', MC4WP_PLUGIN_URL . 'assets/js/third-party/es5-shim.min.js', array(), MC4WP_VERSION );
		$wp_scripts->add_data( 'es5-shim', 'conditional', 'lt IE 9' );

		// @todo: eventually get rid of jQuery here
		wp_register_script( 'mc4wp-admin', MC4WP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery', 'es5-shim' ), MC4WP_VERSION, true );

		wp_enqueue_script( array( 'jquery', 'es5-shim', 'mc4wp-admin' ) );

		wp_localize_script( 'mc4wp-admin', 'mc4wp_vars',
			array(
				'mailchimp' => array(
					'lists' => $this->mailchimp->get_lists()
				),
				'countries' => MC4WP_Tools::get_countries(),
				'l10n' => array(
					'pro_only' => __( 'This is a pro-only feature. Please upgrade to the premium version to be able to use it.', 'mailchimp-for-wp' )
				)
			)
		);

		/**
		 * Hook to enqueue your own custom assets on the MailChimp for WordPress setting pages.
		 *
		 * @since 3.0
		 *
		 * @param string $suffix
		 * @param string $page
		 */
		do_action( 'mc4wp_admin_enqueue_assets', $suffix, $page );

		return true;
	}

	/**
	 * Does the logged-in user have the required capability?
	 *
	 * @return bool
	 */
	public function is_user_authorized() {
		return current_user_can( $this->get_required_capability() );
	}

	/**
	 * Get required capability to access settings page and view dashboard widgets.
	 *
	 * @return string
	 */
	public function get_required_capability() {

		$capability = 'manage_options';

		/**
		 * Filters the required user capability to access the settings pages & dashboard widgets.
		 *
		 * @ignore
		 * @deprecated 3.0
		 */
		$capability = apply_filters( 'mc4wp_settings_cap', $capability );

		/**
		 * Filters the required user capability to access the MailChimp for WordPress' settings pages, view the dashboard widgets.
		 *
		 * Defaults to `manage_options`
		 *
		 * @since 3.0
		 * @param string $capability
		 * @see https://codex.wordpress.org/Roles_and_Capabilities
		 */
		$capability = (string) apply_filters( 'mc4wp_admin_required_capability', $capability );

		return $capability;
	}

	/**
	 * Register the setting pages and their menu items
	 */
	public function build_menu() {
		$required_cap = $this->get_required_capability();

		$menu_items = array(
			'general' => array(
				'title' => __( 'MailChimp API Settings', 'mailchimp-for-wp' ),
				'text' => __( 'MailChimp', 'mailchimp-for-wp' ),
				'slug' => '',
				'callback' => array( $this, 'show_generals_setting_page' ),
				'position' => 0
			)
		);

		/**
		 * Filters the menu items to appear under the main menu item.
		 *
		 * To add your own item, add an associative array in the following format.
		 *
		 * $menu_items[] = array(
		 *     'title' => 'Page title',
		 *     'text'  => 'Menu text',
		 *     'slug' => 'Page slug',
		 *     'callback' => 'my_page_function',
		 *     'position' => 50
		 * );
		 *
		 * @param array $menu_items
		 * @since 3.0
		 */
		$menu_items = (array) apply_filters( 'mc4wp_admin_menu_items', $menu_items );

		// add top menu item
		add_menu_page( 'MailChimp for WP', 'MailChimp for WP', $required_cap, 'mailchimp-for-wp', array( $this, 'show_generals_setting_page' ), MC4WP_PLUGIN_URL . 'assets/img/icon.png', '99.68491' );

		// sort submenu items by 'position'
		uasort( $menu_items, array( $this, 'sort_menu_items_by_position' ) );

		// add sub-menu items
		array_walk( $menu_items, array( $this, 'add_menu_item' ) );
	}

	/**
	 * @param array $item
	 */
	public function add_menu_item( array $item ) {

		// generate menu slug
		$slug = 'mailchimp-for-wp';
		if( ! empty( $item['slug'] ) ) {
			$slug .= '-' . $item['slug'];
		}

		// provide some defaults
		$parent_slug = ! empty( $item['parent_slug']) ? $item['parent_slug'] : 'mailchimp-for-wp';
		$capability = ! empty( $item['capability'] ) ? $item['capability'] : $this->get_required_capability();

		// register page
		$hook = add_submenu_page( $parent_slug, $item['title'] . ' - MailChimp for WordPress', $item['text'], $capability, $slug, $item['callback'] );

		// register callback for loading this page, if given
		if( array_key_exists( 'load_callback', $item ) ) {
			add_action( 'load-' . $hook, $item['load_callback'] );
		}
	}

	/**
	 * Show the API settings page
	 */
	public function show_generals_setting_page() {
		$opts = mc4wp_get_options();
		$connected = ( mc4wp('api')->is_connected() );
		$lists = $this->mailchimp->get_lists();

		require MC4WP_PLUGIN_DIR . 'includes/views/general-settings.php';
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function sort_menu_items_by_position( $a, $b ) {
		$pos_a = isset( $a['position'] ) ? $a['position'] : 80;
		$pos_b = isset( $b['position'] ) ? $b['position'] : 90;
		return $pos_a < $pos_b ? -1 : 1;
	}

}