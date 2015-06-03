<?php

class MC4WP_Admin {

	/**
	 * @var bool True if the BWS Captcha plugin is activated.
	 */
	protected $has_captcha_plugin = false;

	/**
	 * @var string The relative path to the main plugin file from the plugins dir
	 */
	protected $plugin_slug;

	/**
	 * Constructor
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_slug = plugin_basename( $plugin_file );

		// store whether this plugin has the BWS captcha plugin running (https://wordpress.org/plugins/captcha/)
		$this->has_captcha_plugin = function_exists( 'cptch_display_captcha_custom' );
	}

	/**
	 * Registers all hooks
	 */
	public function add_hooks() {

		global $pagenow;
		$current_page = isset( $pagenow ) ? $pagenow : '';

		// Actions used globally throughout WP Admin
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		// Hooks for Plugins overview page
		if( $current_page === 'plugins.php' ) {
			add_filter( 'plugin_action_links_' . $this->plugin_slug, array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );
		}

	}

	/**
	 * Load the plugin translations
	 */
	public function load_translations() {
		// load the plugin text domain
		return load_plugin_textdomain( 'mailchimp-for-wp', false, dirname( MC4WP_PLUGIN_FILE ) . '/languages' );
	}

	/**
	 * Initializes various stuff used in WP Admin
	 *
	 * - Registers settings
	 * - Checks if the Captcha plugin is activated
	 * - Loads the plugin text domain
	 */
	public function init() {
		$this->register_settings();
		$this->load_upgrader();
	}

	protected function register_settings() {
		register_setting( 'mc4wp_settings', 'mc4wp', array( $this, 'validate_settings' ) );
		register_setting( 'mc4wp_checkbox_settings', 'mc4wp_checkbox', array( $this, 'validate_settings' ) );
		register_setting( 'mc4wp_form_settings', 'mc4wp_form', array( $this, 'validate_settings' ) );
	}

	/**
	 * Upgrade routine
	 */
	protected function load_upgrader() {

		// Only run if db option is at older version than code constant
		$db_version = get_option( 'mc4wp_version', 0 );
		if( version_compare( MC4WP_VERSION, $db_version, '<=' ) ) {
			return false;
		}

		$upgrader = new MC4WP_DB_Upgrader( MC4WP_VERSION, $db_version );
		$upgrader->run();
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @param       $slug
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $slug ) {
		if( $slug !== $this->plugin_slug ) {
			return $links;
		}

		 $settings_link = '<a href="' . admin_url( 'admin.php?page=mailchimp-for-wp' ) . '">'. __( 'Settings', 'mailchimp-for-wp' ) . '</a>';
		 array_unshift( $links, $settings_link );
		 return $links;
	}

	/**
	 * Adds meta links to the plugin in the WP Admin > Plugins screen
	 *
	 * @param array $links
	 * @param string $slug
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $slug ) {
		if( $slug !== $this->plugin_slug ) {
			return $links;
		}

		$links[] = '<a href="https://mc4wp.com/kb/">' . __( 'Documentation', 'mailchimp-for-wp' ) . '</a>';
		return $links;
	}

	/**
	* Register the setting pages and their menu items
		*/
	public function build_menu() {

		/**
		 * @filter mc4wp_settings_cap
		 * @expects     string      A valid WP capability like 'manage_options' (default)
		 *
		 * Use to customize the required user capability to access the MC4WP settings pages
		 */
		$required_cap = apply_filters( 'mc4wp_settings_cap', 'manage_options' );

		$menu_items = array(
			'general' => array(
				'title' => __( 'MailChimp API Settings', 'mailchimp-for-wp' ),
				'text' => __( 'MailChimp', 'mailchimp-for-wp' ),
				'slug' => '',
				'callback' => array( $this, 'show_api_settings' ),
			),
			'checkbox' => array(
				'title' => __( 'Checkbox Settings', 'mailchimp-for-wp' ),
				'text' => __( 'Checkboxes', 'mailchimp-for-wp' ),
				'slug' => 'checkbox-settings',
				'callback' => array( $this, 'show_checkbox_settings' ),
			),
			'form' => array(
				'title' => __( 'Form Settings', 'mailchimp-for-wp' ),
				'text' => __( 'Forms', 'mailchimp-for-wp' ),
				'slug' => 'form-settings',
				'callback' => array( $this, 'show_form_settings' ) )
		);

		/**
		 * @api
		 * @filter 'mc4wp_menu_items'
		 * @expects array
		 */
		$menu_items = apply_filters( 'mc4wp_menu_items', $menu_items );

		// add top menu item
		add_menu_page( 'MailChimp for WP', 'MailChimp for WP', $required_cap, 'mailchimp-for-wp', array( $this, 'show_api_settings' ), MC4WP_PLUGIN_URL . 'assets/img/menu-icon.png', '99.68491' );

		// add submenu pages
		foreach( $menu_items as $item ) {
			$slug = ( '' !== $item['slug'] ) ? "mailchimp-for-wp-{$item['slug']}" : 'mailchimp-for-wp';
			add_submenu_page( 'mailchimp-for-wp', $item['title'] . ' - MailChimp for WordPress Lite', $item['text'], $required_cap, $slug, $item['callback'] );
		}

	}


	/**
	* Validates the General settings
	*
	* @param array $settings
	* @return array
	*/
	public function validate_settings( array $settings ) {

		// sanitize simple text fields (no HTML, just chars & numbers)
		$simple_text_fields = array( 'api_key', 'redirect', 'css' );
		foreach( $simple_text_fields as $field ) {
			if( isset( $settings[ $field ] ) ) {
				$settings[ $field ] = sanitize_text_field( $settings[ $field ] );
			}
		}

		// validate woocommerce checkbox position
		if( isset( $settings['woocommerce_position'] ) ) {
			// make sure position is either 'order' or 'billing'
			if( ! in_array( $settings['woocommerce_position'], array( 'order', 'billing' ) ) ) {
				$settings['woocommerce_position'] = 'billing';
			}
		}

		// dynamic sanitization
		foreach( $settings as $setting => $value ) {
			// strip special tags from text settings
			if( substr( $setting, 0, 5 ) === 'text_' || $setting === 'label' ) {
				$value = trim( $value );
				$value = strip_tags( $value, '<a><b><strong><em><i><br><u><script><span><abbr><strike>' );
				$settings[ $setting ] = $value;
			}
		}

		// strip <form> from form mark-up
		if( isset( $settings[ 'markup'] ) ) {
			$settings[ 'markup' ] = preg_replace( '/<\/?form(.|\s)*?>/i', '', $settings[ 'markup'] );
		}

		return $settings;
	}

	/**
	 * Load scripts and stylesheet on MailChimp for WP Admin pages
	 * @return bool
	*/
	public function assets() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// register scripts which are also used by add-on plugins
		wp_register_style( 'mc4wp-admin', MC4WP_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css' );
		wp_register_script( 'mc4wp-beautifyhtml', MC4WP_PLUGIN_URL . 'assets/js/third-party/beautify-html'. $suffix .'.js', array( 'jquery' ), MC4WP_VERSION, true );
		wp_register_script( 'mc4wp-form-helper', MC4WP_PLUGIN_URL . 'assets/js/form-helper' . $suffix . '.js', array( 'jquery', 'mc4wp-beautifyhtml' ), MC4WP_VERSION, true );
		wp_register_script( 'mc4wp-admin', MC4WP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery', 'quicktags', 'mc4wp-form-helper' ), MC4WP_VERSION, true );

		// only load asset files on the MailChimp for WordPress settings pages
		if( strpos( $this->get_current_page(), 'mailchimp-for-wp' ) === 0 ) {
			$mailchimp = new MC4WP_MailChimp();
			$strings = include MC4WP_PLUGIN_DIR . 'config/js-strings.php';

			// css
			wp_enqueue_style( 'mc4wp-admin' );

			// js
			wp_enqueue_script( 'mc4wp-admin' );
			wp_localize_script( 'mc4wp-admin', 'mc4wp',
				array(
					'hasCaptchaPlugin' => $this->has_captcha_plugin,
					'strings' => $strings,
					'mailchimpLists' => $mailchimp->get_lists()
				)
			);

			return true;
		}

		return false;
	}

	/**
	 * Returns available checkbox integrations
	 *
	 * @return array
	 */
	public function get_checkbox_compatible_plugins() {
		static $checkbox_plugins;

		if( is_array( $checkbox_plugins ) ) {
			return $checkbox_plugins;
		}

		$checkbox_plugins = array(
			'comment_form' => __( 'Comment form', 'mailchimp-for-wp' ),
			'registration_form' => __( 'Registration form', 'mailchimp-for-wp' )
		);

		if( is_multisite() ) {
			$checkbox_plugins['multisite_form'] = __( 'MultiSite forms', 'mailchimp-for-wp' );
		}

		if( class_exists( 'BuddyPress' ) ) {
			$checkbox_plugins['buddypress_form'] = __( 'BuddyPress registration', 'mailchimp-for-wp' );
		}

		if( class_exists( 'bbPress' ) ) {
			$checkbox_plugins['bbpress_forms'] = 'bbPress';
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$checkbox_plugins['woocommerce_checkout'] = sprintf( __( '%s checkout', 'mailchimp-for-wp' ), 'WooCommerce' );
		}

		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			$checkbox_plugins['edd_checkout'] = sprintf( __( '%s checkout', 'mailchimp-for-wp' ), 'Easy Digital Downloads' );
		}

		return $checkbox_plugins;
	}

	/**
	* Show the API settings page
	*/
	public function show_api_settings()
	{
		$opts = mc4wp_get_options( 'general' );
		$connected = ( mc4wp()->get_api()->is_connected() );

		// cache renewal triggered manually?
		$force_cache_refresh = isset( $_POST['mc4wp-renew-cache'] ) && $_POST['mc4wp-renew-cache'] == 1;
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists( $force_cache_refresh );

		if( $lists && count( $lists ) === 100 ) {
			add_settings_error( 'mc4wp', 'mc4wp-lists-at-limit', __( 'The plugin can only fetch a maximum of 100 lists from MailChimp, only your first 100 lists are shown.', 'mailchimp-for-wp' ) );
		}

		if ( $force_cache_refresh ) {
			if ( false === empty ( $lists ) ) {
				add_settings_error( 'mc4wp', 'mc4wp-cache-success', __( 'Renewed MailChimp cache.', 'mailchimp-for-wp' ), 'updated' );
			} else {
				add_settings_error( 'mc4wp', 'mc4wp-cache-error', __( 'Failed to renew MailChimp cache - please try again later.', 'mailchimp-for-wp' ) );
			}
		}

		require MC4WP_PLUGIN_DIR . 'includes/views/general-settings.php';
	}

	/**
	* Show the Checkbox settings page
	*/
	public function show_checkbox_settings()
	{
		$mailchimp = new MC4WP_MailChimp();
		$opts = mc4wp_get_options( 'checkbox' );
		$lists = $mailchimp->get_lists();
		require MC4WP_PLUGIN_DIR . 'includes/views/checkbox-settings.php';
	}

	/**
	* Show the forms settings page
	*/
	public function show_form_settings()
	{
		$opts = mc4wp_get_options( 'form' );
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists();

		require MC4WP_PLUGIN_DIR . 'includes/views/form-settings.php';
	}

	/**
	 * @return string
	 */
	protected function get_current_page() {
		return isset( $_GET['page'] ) ? $_GET['page'] : '';
	}

}