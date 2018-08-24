<?php

/**
* Class PL4WP_Admin
*
* @ignore
* @access private
*/
class PL4WP_Admin {

	/**
	* @var string The relative path to the main plugin file from the plugins dir
	*/
	protected $plugin_file;

	/**
	* @var PL4WP_PhpList
	*/
	protected $phplist;

	/**
	* @var PL4WP_Admin_Messages
	*/
	protected $messages;

	/**
	* @var PL4WP_Admin_Ads
	*/
	protected $ads;

	/**
	* @var PL4WP_Admin_Tools
	*/
	protected $tools;

	/**
	* @var PL4WP_Admin_Review_Notice
	*/
	protected $review_notice;

	/**
	* Constructor
	*
	* @param PL4WP_Admin_Tools $tools
	* @param PL4WP_Admin_Messages $messages
	* @param PL4WP_PhpList      $phplist
	*/
	public function __construct( PL4WP_Admin_Tools $tools, PL4WP_Admin_Messages $messages, PL4WP_PhpList $phplist ) {
		$this->tools = $tools;
		$this->phplist = $phplist;
		$this->messages = $messages;
		$this->plugin_file = plugin_basename( PL4WP_PLUGIN_FILE );
		$this->ads = new PL4WP_Admin_Ads();
		$this->review_notice = new PL4WP_Admin_Review_Notice( $tools );
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
		add_action( 'pl4wp_admin_empty_lists_cache', array( $this, 'renew_lists_cache' ) );
		add_action( 'pl4wp_admin_empty_debug_log', array( $this, 'empty_debug_log' ) );

		add_action( 'admin_notices', array( $this, 'show_api_key_notice' ) );
		add_action( 'pl4wp_admin_dismiss_api_key_notice', array( $this, 'dismiss_api_key_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$this->ads->add_hooks();
		$this->messages->add_hooks();
		$this->review_notice->add_hooks();
	}

	/**
	* Initializes various stuff used in WP Admin
	*
	* - Registers settings
	*/
	public function initialize() {

		// register settings
		register_setting( 'pl4wp_settings', 'pl4wp', array( $this, 'save_general_settings' ) );

		// Load upgrader
		$this->init_upgrade_routines();

		// listen for custom actions
		$this->listen_for_actions();
	}


	/**
	* Listen for `_pl4wp_action` requests
	*/
	public function listen_for_actions() {

		// listen for any action (if user is authorised)
		if( ! $this->tools->is_user_authorized() || ! isset( $_REQUEST['_pl4wp_action'] ) ) {
			return false;
		}

		$action = (string) $_REQUEST['_pl4wp_action'];

		/**
		* Allows you to hook into requests containing `_pl4wp_action` => action name.
		*
		* The dynamic portion of the hook name, `$action`, refers to the action name.
		*
		* By the time this hook is fired, the user is already authorized. After processing all the registered hooks,
		* the request is redirected back to the referring URL.
		*
		* @since 3.0
		*/
		do_action( 'pl4wp_admin_' . $action );

		// redirect back to where we came from
		$redirect_url = ! empty( $_POST['_redirect_to'] ) ? $_POST['_redirect_to'] : remove_query_arg( '_pl4wp_action' );
		wp_redirect( $redirect_url );
		exit;
	}

	/**
	* Register dashboard widgets
	*/
	public function register_dashboard_widgets() {

		if( ! $this->tools->is_user_authorized() ) {
			return false;
		}

		/**
		* Setup dashboard widget, users are authorized by now.
		*
		* Use this hook to register your own dashboard widgets for users with the required capability.
		*
		* @since 3.0
		* @ignore
		*/
		do_action( 'pl4wp_dashboard_setup' );

		return true;
	}

	/**
	* Upgrade routine
	*/
	private function init_upgrade_routines() {

		// upgrade routine for upgrade routine....
		$previous_version = get_option( 'pl4wp_lite_version', 0 );
		if( $previous_version ) {
			delete_option( 'pl4wp_lite_version' );
			update_option( 'pl4wp_version', $previous_version );
		}

		$previous_version = get_option( 'pl4wp_version', 0 );

		// allow setting migration version from URL, to easily re-run previous migrations.
		if( isset( $_GET['pl4wp_run_migration'] ) ) {
			$previous_version = $_GET['pl4wp_run_migration'];
		}

		// Ran upgrade routines before?
		if( empty( $previous_version ) ) {
			update_option( 'pl4wp_version', PL4WP_VERSION );

			// if we have at least one form, we're going to run upgrade routine for v3 => v4 anyway.
			// TODO: Remove this once we hit 4.2.x
			$posts = get_posts( array( 'post_type' => 'pl4wp-form', 'numberposts' => 1 ) );
			if( empty( $posts ) ) {
				return false;
			}

			$previous_version = '3.9';
		}

		// Rollback'ed?
		if( version_compare( $previous_version, PL4WP_VERSION, '>' ) ) {
			update_option( 'pl4wp_version', PL4WP_VERSION );
			return false;
		}

		// This means we're good!
		if( version_compare( $previous_version, PL4WP_VERSION ) > -1 ) {
			return false;
		}

		define( 'PL4WP_DOING_UPGRADE', true );
		$upgrade_routines = new PL4WP_Upgrade_Routines( $previous_version, PL4WP_VERSION, dirname( __FILE__ ) . '/migrations' );
		$upgrade_routines->run();
		update_option( 'pl4wp_version', PL4WP_VERSION );
	}

	/**
	* Renew PhpList lists cache
	*/
	public function renew_lists_cache() {
		// try getting new lists to fill cache again
		$lists = $this->phplist->fetch_lists();

		if( ! empty( $lists ) ) {
			$this->messages->flash( __( 'Success! The cached configuration for your PhpList lists has been renewed.', 'phplist-for-wp' ) );
		}
	}

	/**
	* Load the plugin translations
	*/
	private function load_translations() {
		// load the plugin text domain
		load_plugin_textdomain( 'phplist-for-wp', false, dirname( $this->plugin_file ) . '/languages' );
	}

	/**
	* Customize texts throughout WP Admin
	*/
	public function customize_admin_texts() {
		$texts = new PL4WP_Admin_Texts( $this->plugin_file );
		$texts->add_hooks();
	}

	/**
	* Validates the General settings
	* @param array $settings
	* @return array
	*/
	public function save_general_settings( array $settings ) {

		$current = pl4wp_get_options();

		// merge with current settings to allow passing partial arrays to this method
		$settings = array_merge( $current, $settings );

		// toggle usage tracking
		if( $settings['allow_usage_tracking'] !== $current['allow_usage_tracking'] ) {
			PL4WP_Usage_Tracking::instance()->toggle( $settings['allow_usage_tracking'] );
		}

		// Make sure not to use obfuscated key
		if( strpos( $settings['api_key'], '*' ) !== false ) {
			$settings['api_key'] = $current['api_key'];
		}

		// Sanitize API key
		$settings['api_key'] = sanitize_text_field( $settings['api_key'] );

		// if API key changed, empty PhpList cache
		if ( $settings['api_key'] !== $current['api_key'] ) {
			$this->phplist->empty_cache();
		}


		/**
		* Runs right before general settings are saved.
		*
		* @param array $settings The updated settings array
		* @param array $current The old settings array
		*/
		do_action( 'pl4wp_save_settings', $settings, $current );

		return $settings;
	}

	/**
	* Load scripts and stylesheet on PhpList for WP Admin pages
	*
	* @return bool
	*/
	public function enqueue_assets() {

		global $wp_scripts;


		if( ! $this->tools->on_plugin_page() ) {
			return false;
		}

		$opts = pl4wp_get_options();
		$page = $this->tools->get_plugin_page();
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// css
		wp_register_style( 'pl4wp-admin', PL4WP_PLUGIN_URL . 'assets/css/admin-styles' . $suffix . '.css', array(), PL4WP_VERSION );
		wp_enqueue_style( 'pl4wp-admin' );

		// js
		wp_register_script( 'es5-shim', PL4WP_PLUGIN_URL . 'assets/js/third-party/es5-shim.min.js', array(), PL4WP_VERSION );
		$wp_scripts->add_data( 'es5-shim', 'conditional', 'lt IE 9' );

		// TODO: eventually get rid of jQuery here
		wp_register_script( 'pl4wp-admin', PL4WP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery', 'es5-shim' ), PL4WP_VERSION, true );
		wp_enqueue_script( array( 'jquery', 'es5-shim', 'pl4wp-admin' ) );

		wp_localize_script( 'pl4wp-admin', 'pl4wp_vars',
			array(
				'phplist' => array(
					'api_connected' => ! empty( $opts['api_key'] ),
					'lists' => $this->phplist->get_cached_lists()
				),
				'countries' => PL4WP_Tools::get_countries(),
				'i18n' => array(
					'pro_only' => __( 'This is a pro-only feature. Please upgrade to the premium version to be able to use it.', 'phplist-for-wp' ),
					'renew_phplist_lists' => __( 'Renew PhpList lists', 'phplist-for-wp' ),
					'fetching_phplist_lists' => __( 'Fetching PhpList lists', 'phplist-for-wp' ),
					'fetching_phplist_lists_done' => __( 'Done! PhpList lists renewed.', 'phplist-for-wp' ),
					'fetching_phplist_lists_can_take_a_while' => __( 'This can take a while if you have many PhpList lists.', 'phplist-for-wp' ),
					'fetching_phplist_lists_error' => __( 'Failed to renew your lists. An error occured.', 'phplist-for-wp' ),
				)
			)
		);

		/**
		* Hook to enqueue your own custom assets on the PhpList for WordPress setting pages.
		*
		* @since 3.0
		*
		* @param string $suffix
		* @param string $page
		*/
		do_action( 'pl4wp_admin_enqueue_assets', $suffix, $page );

		return true;
	}



	/**
	* Register the setting pages and their menu items
	*/
	public function build_menu() {
		$required_cap = $this->tools->get_required_capability();

		$menu_items = array(
			array(
				'title' => __( 'PhpList API Settings', 'phplist-for-wp' ),
				'text' => __( 'PhpList', 'phplist-for-wp' ),
				'slug' => '',
				'callback' => array( $this, 'show_generals_setting_page' ),
				'position' => 0
			),
			array(
				'title' => __( 'Other Settings', 'phplist-for-wp' ),
				'text' => __( 'Other', 'phplist-for-wp' ),
				'slug' => 'other',
				'callback' => array( $this, 'show_other_setting_page' ),
				'position' => 90
			),

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
		$menu_items = (array) apply_filters( 'pl4wp_admin_menu_items', $menu_items );

		// add top menu item
		add_menu_page( 'PhpList for WP', 'PhpList for WP', $required_cap, 'phplist-for-wp', array( $this, 'show_generals_setting_page' ), PL4WP_PLUGIN_URL . 'assets/img/icon.png', '99.68491' );

		// sort submenu items by 'position'
		usort( $menu_items, array( $this, 'sort_menu_items_by_position' ) );

		// add sub-menu items
		foreach( $menu_items as $item ) {
			$this->add_menu_item( $item );
		}
	}

	/**
	* @param array $item
	*/
	public function add_menu_item( array $item ) {

		// generate menu slug
		$slug = 'phplist-for-wp';
		if( ! empty( $item['slug'] ) ) {
			$slug .= '-' . $item['slug'];
		}

		// provide some defaults
		$parent_slug = ! empty( $item['parent_slug']) ? $item['parent_slug'] : 'phplist-for-wp';
		$capability = ! empty( $item['capability'] ) ? $item['capability'] : $this->tools->get_required_capability();

		// register page
		$hook = add_submenu_page( $parent_slug, $item['title'] . ' - PhpList for WordPress', $item['text'], $capability, $slug, $item['callback'] );

		// register callback for loading this page, if given
		if( array_key_exists( 'load_callback', $item ) ) {
			add_action( 'load-' . $hook, $item['load_callback'] );
		}
	}

	/**
	* Show the API Settings page
	*/
	public function show_generals_setting_page() {
		$opts = pl4wp_get_options();

		$connected = ! empty( $opts['api_key'] );
		if( $connected ) {
			try {
				$connected = $this->get_api()->is_connected();
			} catch( PL4WP_API_Connection_Exception $e ) {
				$message = sprintf( "<strong>%s</strong> %s %s ", __( "Error connecting to PhpList:", 'phplist-for-wp' ), $e->getCode(), $e->getMessage() );

				if( is_object( $e->data ) && ! empty( $e->data->ref_no ) ) {
					$message .= '<br />' . sprintf( __( 'Looks like your server is blocked by PhpList\'s firewall. Please contact PhpList support and include the following reference number: %s', 'phplist-for-wp' ), $e->data->ref_no );
				}

				$message .= '<br /><br />' . sprintf( '<a href="%s">' . __( 'Here\'s some info on solving common connectivity issues.', 'phplist-for-wp' ) . '</a>', 'https://kb.pl4wp.com/solving-connectivity-issues/#utm_source=wp-plugin&utm_medium=phplist-for-wp&utm_campaign=settings-notice' );

				$this->messages->flash( $message, 'error' );
				$connected = false;
			} catch( PL4WP_API_Exception $e ) {
				$this->messages->flash( sprintf( "<strong>%s</strong><br /> %s", __( "PhpList returned the following error:", 'phplist-for-wp' ), $e ), 'error' );
				$connected = false;
			}
		}

		$lists = $this->phplist->get_cached_lists();
		$obfuscated_api_key = pl4wp_obfuscate_string( $opts['api_key'] );
		require PL4WP_PLUGIN_DIR . 'includes/views/general-settings.php';
	}

	/**
	* Show the Other Settings page
	*/
	public function show_other_setting_page() {
		$opts = pl4wp_get_options();
		$log = $this->get_log();
		$log_reader = new PL4WP_Debug_Log_Reader( $log->file );
		require PL4WP_PLUGIN_DIR . 'includes/views/other-settings.php';
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

	/**
	* Empties the log file
	*/
	public function empty_debug_log() {
		$log = $this->get_log();
		file_put_contents( $log->file, '' );

		$this->messages->flash( __( 'Log successfully emptied.', 'phplist-for-wp' ) );
	}

	/**
	* Shows a notice when API key is not set.
	*/
	public function show_api_key_notice() {

		// don't show if on settings page already
		if( $this->tools->on_plugin_page( '' ) ) {
			return;
		}

		// only show to user with proper permissions
		if( ! $this->tools->is_user_authorized() ) {
			return;
		}

		// don't show if dismissed
		if( get_transient( 'pl4wp_api_key_notice_dismissed' ) ) {
			return;
		}

		// don't show if api key is set already
		$options = pl4wp_get_options();
		if( ! empty( $options['api_key'] ) ) {
			return;
		}

		echo '<div class="notice notice-warning pl4wp-is-dismissible">';
		echo '<p>' . sprintf( __( 'To get started with PhpList for WordPress, please <a href="%s">enter your PhpList API key on the settings page of the plugin</a>.', 'phplist-for-wp' ), admin_url( 'admin.php?page=phplist-for-wp' ) ) . '</p>';
		echo '<form method="post"><input type="hidden" name="_pl4wp_action" value="dismiss_api_key_notice" /><button type="submit" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></form>';
		echo '</div>';
	}

	/**
	* Dismisses the API key notice for 1 week
	*/
	public function dismiss_api_key_notice() {
		set_transient( 'pl4wp_api_key_notice_dismissed', 1, 3600 * 24 * 7 );
	}

	/**
	* @return PL4WP_Debug_Log
	*/
	protected function get_log() {
		return pl4wp('log');
	}

	/**
	* @return PL4WP_API_v3
	*/
	protected function get_api() {
		return pl4wp('api');
	}

}
