<?php

class MC4WP_Admin {

	/**
	 * @var string The relative path to the main plugin file from the plugins dir
	 */
	private $plugin_file;

	/**
	 * @var MC4WP_MailChimp
	 */
	protected $mailchimp;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_file = plugin_basename( MC4WP_PLUGIN_FILE );
		$this->mailchimp = new MC4WP_MailChimp();
		$this->ads = new MC4WP_Ads();

		$this->load_translations();
		$this->add_hooks();

		$options = mc4wp_get_options();
		if( ! $options['allow_usage_tracking'] ) {
			$usage_tracking_nag = new MC4WP_Usage_Tracking_Nag( $this->get_required_capability() );
			$usage_tracking_nag->add_hooks();
		}
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

		do_action( 'mc4wp_admin_' . $action );
	}

	/**
	 * Register dashboard widgets
	 */
	public function register_dashboard_widgets() {

		// todo: re-add existing filter
		if( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		do_action( 'mc4wp_dashboard_setup' );

		return true;
	}

	/**
	 * Upgrade routine
	 */
	private function load_upgrader() {

		// Only run if db option is at older version than code constant
		$db_version = get_option( 'mc4wp_lite_version', 0 );
		if( version_compare( MC4WP_VERSION, $db_version, '<=' ) ) {
			return false;
		}

		$upgrader = new MC4WP_DB_Upgrader( MC4WP_VERSION, $db_version );
		$upgrader->run();
	}

	/**
	 * Act on the "add form" form
	 */
	public function process_add_form() {

		check_admin_referer( 'add_form', '_mc4wp_nonce' );

		$form_data = stripslashes_deep( $_POST['mc4wp_form'] );
		$form_content = include MC4WP_PLUGIN_DIR . 'config/default-form-content.php';
		$form_id = wp_insert_post(
			array(
				'post_type' => 'mc4wp-form',
				'post_status' => 'publish',
				'post_title' => $form_data['name'],
				'post_content' => $form_content,
			)
		);

		update_post_meta( $form_id, '_mc4wp_settings', $form_data['settings'] );

		// @todo allow for easy way to get admin url's
		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => 'mailchimp-for-wp-forms',
					'view' => 'edit-form',
					'form_id' => $form_id,
					'message' => 'form_updated'
				)
			)
		);
		exit;
	}

	/**
	 * Saves a form
	 */
	public function process_save_form() {

		if( ! check_admin_referer( 'edit_form', '_mc4wp_nonce' ) ) {
			wp_die( "Are you cheating?" );
		}

		$form_id = (int) $_POST['mc4wp_form_id'];
		$form_data = stripslashes_deep( $_POST['mc4wp_form'] );
		$form_settings = $form_data['settings'];
		// @todo sanitize data

		// get actual form id here since this might be a new form
		// @todo prevent overriding existing posts using $_GET parameter
		$form_id = wp_insert_post(
			array(
				'ID' => $form_id,
				'post_type' => 'mc4wp-form',
				'post_status' => 'publish',
				'post_title' => $form_data['name'],
				'post_content' => $form_data['content']
			)
		);

		update_post_meta( $form_id, '_mc4wp_settings', $form_settings );

		// save form messages in individual meta keys
		foreach( $form_data['messages'] as $key => $message ) {
			update_post_meta( $form_id, $key, $message );
		}

		// update default form id?
		$default_form_id = (int) get_option( 'mc4wp_default_form_id', 0 );
		if( empty( $default_form_id ) ) {
			update_option( 'mc4wp_default_form_id', $form_id );
		}

		// update form stylesheets
		// @todo this should loop through all forms and find used stylesheets, otherwise this would fill up indefinitely
		if( ! empty( $form_settings['css'] ) ) {

			$stylesheet = $form_settings['css'];
			if( strpos( $stylesheet, 'form-theme' ) !== false ) {
				$stylesheet = 'form-themes';
			}
			$stylesheets = (array) get_option( 'mc4wp_form_stylesheets', array() );

			if( ! in_array( $stylesheet, $stylesheets ) ) {
				$stylesheets[] = $stylesheet;
			}

			update_option( 'mc4wp_form_stylesheets', $stylesheets );
		}

		wp_safe_redirect( add_query_arg( array( 'form_id' => $form_id, 'message' => 'form_updated' ) ) );
		exit;
	}

	/**
	 * Registers all hooks
	 */
	private function add_hooks() {

		// Actions used globally throughout WP Admin
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'current_screen', array( $this, 'customize_admin_texts' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
		add_action( 'mc4wp_admin_edit_form', array( $this, 'process_save_form' ) );
		add_action( 'mc4wp_admin_add_form', array( $this, 'process_add_form' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$this->ads->add_hooks();
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
	 * Initializes various stuff used in WP Admin
	 *
	 * - Registers settings
	 * - Checks if the Captcha plugin is activated
	 * - Loads the plugin text domain
	 */
	public function initialize() {

		// register settings
		register_setting( 'mc4wp_settings', 'mc4wp', array( $this, 'save_general_settings' ) );
		register_setting( 'mc4wp_integrations_settings', 'mc4wp_integrations', array( $this, 'save_integration_settings' ) );

		// Load upgrader
		$this->load_upgrader();

		// listen for custom actions
		$this->listen_for_actions();
	}



	/**
	 * Redirect to correct form action
	 *
	 * @todo Use `default_form_id` here?
	 */
	public function redirect_to_form_action() {

		if( ! empty( $_GET['view'] ) ) {
			return;
		}

		// query first available form and go there
		$posts = get_posts(
			array(
				'post_type' => 'mc4wp-form',
				'post_status' => 'publish',
				'numberposts' => 1
			)
		);

		// if we have a post, go to the "edit form" screen
		if( $posts ) {
			$post = array_pop( $posts );
			$edit_form_url = add_query_arg(
				array(
					'view' => 'edit-form',
					'form_id' => $post->ID
				)
			);
			wp_safe_redirect( $edit_form_url );
			exit;
		}

		// we don't have a form yet, go to "add new" screen
		$add_form_url = add_query_arg( array( 'view' => 'add-form' ) );
		wp_safe_redirect( $add_form_url );
		exit;
	}

	/**
	* Validates the General settings
	*
	 * @todo split-up this method
	* @param array $settings
	* @return array
	*/
	public function save_general_settings( array $settings ) {

		$current = mc4wp_get_options();

		// Toggle usage tracking
		if( isset( $settings['allow_usage_tracking'] ) ) {
			MC4WP_Usage_Tracking::instance()->toggle( (bool) $settings['allow_usage_tracking'] );
		}

		// sanitize simple text fields (no HTML, just chars & numbers)
		$simple_text_fields = array( 'api_key', 'redirect', 'css' );
		foreach( $simple_text_fields as $field ) {
			if( isset( $settings[ $field ] ) ) {
				$settings[ $field ] = sanitize_text_field( $settings[ $field ] );
			}
		}

		// if api key changed, empty cache
		if( isset( $settings['api_key'] ) && $settings['api_key'] !== $current['general']['api_key'] ) {
			$this->mailchimp->empty_cache();
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
	 * @todo perform some validation
	 * @internal
	 * @since 3.0
	 * @param array $new_settings
	 * @return array
	 */
	public function save_integration_settings( array $new_settings ) {

		$integrations = mc4wp_get_integrations();
		$current_settings = (array) get_option( 'mc4wp_integrations', array() );
		$settings = array();

		foreach( $integrations as $slug => $integration ) {
			$settings[ $slug ] = $this->parse_integration_settings( $slug, $current_settings, $new_settings );
		}

		return $settings;
	}

	/**
	 * @internal
	 * @since 3.0
	 * @param $slug
	 * @param $current_settings
	 * @param $new_settings
	 *
	 * @return array
	 */
	protected function parse_integration_settings( $slug, $current_settings, $new_settings ) {
		$settings = array();

		// start with current settings
		if( ! empty( $current_settings[ $slug ] ) ) {
			$settings = $current_settings[ $slug ];
		}

		// then, merge with new settings
		if( ! empty( $new_settings[ $slug ] ) ) {
			$settings = array_merge( $settings, $new_settings[ $slug ] );
		}

		return $settings;
	}

	/**
	 * Load scripts and stylesheet on MailChimp for WP Admin pages
	 * @return bool
	*/
	public function enqueue_assets() {
		// only load asset files on the MailChimp for WordPress settings pages
		if( empty( $_GET['page'] ) || strpos( $_GET['page'], 'mailchimp-for-wp' ) !== 0 ) {
			return false;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// css
		wp_enqueue_style( 'mc4wp-admin', MC4WP_PLUGIN_URL . 'assets/css/admin-styles' . $suffix . '.css', array(), MC4WP_VERSION );
		wp_enqueue_style( 'codemirror', MC4WP_PLUGIN_URL . 'assets/css/codemirror.css', array(), MC4WP_VERSION );

		// js
		wp_register_script( 'mc4wp-beautifyhtml', MC4WP_PLUGIN_URL . 'assets/js/third-party/beautify-html'. $suffix .'.js', array( 'jquery' ), MC4WP_VERSION, true );
		wp_register_script( 'mc4wp-admin', MC4WP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery' ), MC4WP_VERSION, true );
		wp_register_script( 'codemirror', MC4WP_PLUGIN_URL . 'assets/js/third-party/codemirror-compressed.js', array(), MC4WP_VERSION, true );

		wp_enqueue_script( array( 'jquery', 'codemirror', 'mc4wp-beautifyhtml', 'mc4wp-admin' ) );
		wp_localize_script( 'mc4wp-admin', 'mc4wp',
			array(
				'strings' => array(
					'proOnlyNotice' => __( 'This option is only available in MailChimp for WordPress Pro.', 'mailchimp-for-wp' ),
					'fieldWizard' => array(
						'proOnly' => __( '(PRO ONLY)', 'mailchimp-for-wp' ),
						'buttonText' => __( 'Button text', 'mailchimp-for-wp' ),
						'initialValue' => __( 'Initial value', 'mailchimp-for-wp' ),
						'optional' => __( '(optional)', 'mailchimp-for-wp' ),
						'labelFor' => __( 'Label for', 'mailchimp-for-wp' ),
						'orLeaveEmpty' => __( '(or leave empty)', 'mailchimp-for-wp' ),
						'subscribe' => __( 'Subscribe', 'mailchimp-for-wp' ),
						'unsubscribe' => __( 'Unsubscribe', 'mailchimp-for-wp' ),
					)
				),
				'mailchimpLists' => $this->mailchimp->get_lists()
			)
		);

		do_action( 'mc4wp_admin_enqueue_assets', $suffix );

		return true;
	}

	/**
	 * @return string
	 */
	public function get_required_capability() {

		/**
		 * @filter mc4wp_settings_cap
		 * @expects     string      A valid WP capability like 'manage_options' (default)
		 *
		 * Use to customize the required user capability to access the MC4WP settings pages
		 */
		$required_cap = (string) apply_filters( 'mc4wp_settings_cap', 'manage_options' );

		return $required_cap;
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
			),
			'integrations' => array(
				'title' => __( 'Integrations', 'mailchimp-for-wp' ),
				'text' => __( 'Integrations', 'mailchimp-for-wp' ),
				'slug' => 'integrations',
				'callback' => array( $this, 'show_integrations_page' ),
			),
			'forms' => array(
				'title' => __( 'Forms', 'mailchimp-for-wp' ),
				'text' => __( 'Forms', 'mailchimp-for-wp' ),
				'slug' => 'forms',
				'callback' => array( $this, 'show_forms_page' ),
				'load_callback' => array( $this, 'redirect_to_form_action' )
			),
		);

		$menu_items = (array) apply_filters( 'mc4wp_menu_items', $menu_items );

		// add top menu item
		add_menu_page( 'MailChimp for WP', 'MailChimp for WP', $required_cap, 'mailchimp-for-wp', array( $this, 'show_generals_setting_page' ), MC4WP_PLUGIN_URL . 'assets/img/icon.png', '99.68491' );

		// add submenu pages
		foreach( $menu_items as $item ) {
			$this->add_menu_item( $item );
		}
	}

	/**
	 * @param array $item
	 */
	public function add_menu_item( array $item ) {

		// provide some defaults
		$slug = ! empty( $item['slug'] ) ? "mailchimp-for-wp-{$item['slug']}" : 'mailchimp-for-wp';
		$parent_slug = array_key_exists( 'parent_slug', $item ) ? $item['parent_slug'] : 'mailchimp-for-wp';
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
		$connected = ( mc4wp_get_api()->is_connected() );

		// cache renewal triggered manually?
		$force_cache_refresh = isset( $_POST['mc4wp-renew-cache'] ) && $_POST['mc4wp-renew-cache'] == 1;
		$lists = $this->mailchimp->get_lists( $force_cache_refresh );

		// @todo make this pretty
		if ( $force_cache_refresh ) {

			if( is_array( $lists ) ) {
				if( count( $lists ) === 100 ) {
					add_settings_error( 'mc4wp', 'mc4wp-lists-at-limit', __( 'The plugin can only fetch a maximum of 100 lists from MailChimp, only your first 100 lists are shown.', 'mailchimp-for-wp' ) );
				} else {
					add_settings_error( 'mc4wp', 'mc4wp-cache-success', __( 'Renewed MailChimp cache.', 'mailchimp-for-wp' ), 'updated' );
				}
			} else {
				add_settings_error( 'mc4wp', 'mc4wp-cache-error', __( 'Failed to renew MailChimp cache - please try again later.', 'mailchimp-for-wp' ) );
			}

		}

		require MC4WP_PLUGIN_DIR . 'includes/views/general-settings.php';
	}

	/**
	 * Show the Integration Settings page
	 */
	public function show_integrations_page() {

		if( ! empty( $_GET['integration'] ) ) {
			$this->show_integration_settings_page( $_GET['integration'] );
			return;
		}

		$integrations = mc4wp_get_integrations();

		require MC4WP_PLUGIN_DIR . 'includes/views/integrations.php';
	}

	/**
	 * @param string $slug
	 */
	public function show_integration_settings_page( $slug ) {

		try {
			$integration = mc4wp_get_integration( $slug );
		} catch( Exception $e ) {
			echo sprintf( '<h3>Integration not found.</h3><p>No integration with slug <strong>%s</strong> was found.</p>', $slug );
			return;
		}

		$opts = $integration->options;
		$lists = $this->mailchimp->get_lists();

		require MC4WP_PLUGIN_DIR . 'includes/views/integration-settings.php';
	}

	/**
	 * Show the Forms Settings page
	 */
	public function show_forms_page() {

		// if a view is set in the URl, go there.
		$view = ( ! empty( $_GET['view'] ) ) ? str_replace( '-', '_', $_GET['view'] ) : '';
		$view_method = 'show_forms_' . $view. '_page';
		if( method_exists( $this, $view_method ) ) {
			return call_user_func( array( $this, $view_method ) );
		}

		// render forms overview

	}

	/**
	 * Show the "Edit Form" page
	 */
	public function show_forms_edit_form_page() {
		$form_id = ( ! empty( $_GET['form_id'] ) ) ? (int) $_GET['form_id'] : 0;
		$lists = $this->mailchimp->get_lists();

		try{
			$form = mc4wp_get_form( $form_id );
		} catch( Exception $e ) {
			echo '<h3>' . __( "Form not found.", 'mailchimp-for-wp' ) . '</h3>';
			echo '<p>' . $e->getMessage() . '</p>';
			return;
		}

		$opts = $form->settings;
		$active_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'fields';
		$previewer = new MC4WP_Form_Previewer( $form->ID );

		require MC4WP_PLUGIN_DIR . 'includes/views/edit-form.php';
	}

	/**
	 * Shows the "Add Form" page
	 */
	public function show_forms_add_form_page() {
		$lists = $this->mailchimp->get_lists();
		require MC4WP_PLUGIN_DIR . 'includes/views/add-form.php';
	}

	/**
	 * @param $tab
	 *
	 * @return string
	 */
	public function tab_url( $tab ) {
		return add_query_arg( array( 'tab' => $tab ), remove_query_arg( 'tab' ) );
	}

	/**
	 * @return string
	 */
	public function admin_messages() {

		if( empty( $_GET['message'] ) ) {
			return;
		}

		$message_index = (string) $_GET['message'];
		$messages = array(
			'form_updated' => __( "<strong>Success!</strong> Form successfully saved.", 'mailchimp-for-wp' )
		);


		if( ! empty( $messages[ $message_index ] ) ) {
			echo sprintf( '<div class="notice updated is-dismissible"><p>%s</p></div>', $messages[ $message_index ] );
		};
	}



}