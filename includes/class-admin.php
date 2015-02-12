<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite_Admin
{

	/**
	 * @var bool True if the BWS Captcha plugin is activated.
	 */
	private $has_captcha_plugin = false;

	/**
	 * @var string The relative path to the main plugin file from the plugins dir
	 */
	private $plugin_file;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->plugin_file = plugin_basename( MC4WP_LITE_PLUGIN_FILE );

		$this->load_translations();
		$this->setup_hooks();
		$this->listen();
	}

	/**
	 * Upgrade routine
	 */
	private function upgrade() {

		// Only run if db option is at older version than code constant
		$db_version = get_option( 'mc4wp_lite_version', 0 );
		if( version_compare( MC4WP_LITE_VERSION, $db_version, '<=' ) ) {
			return false;
		}

		// define a constant that we're running an upgrade
		define( 'MC4WP_DOING_UPGRADE', true );

		// update code version
		update_option( 'mc4wp_lite_version', MC4WP_LITE_VERSION );
	}

	/**
	 * Registers all hooks
	 */
	private function setup_hooks() {

		global $pagenow;
		$current_page = isset( $pagenow ) ? $pagenow : '';

		// Actions used globally throughout WP Admin
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_css_and_js' ) );

		// Hooks for Plugins overview page
		if( $current_page === 'plugins.php' ) {
			$this->plugin_file = plugin_basename( MC4WP_LITE_PLUGIN_FILE );

			add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );
		}

		// Hooks for Form settings page
		if( isset( $_GET['page'] ) && $_GET['page'] === 'mailchimp-for-wp-form-settings' ) {
			add_filter( 'quicktags_settings', array( $this, 'set_quicktags_buttons' ), 10, 2 );
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
	 * Initializes various stuff used in WP Admin
	 *
	 * - Registers settings
	 * - Checks if the Captcha plugin is activated
	 * - Loads the plugin text domain
	 */
	public function initialize() {

		// register settings
		register_setting( 'mc4wp_lite_settings', 'mc4wp_lite', array( $this, 'validate_settings' ) );
		register_setting( 'mc4wp_lite_checkbox_settings', 'mc4wp_lite_checkbox', array( $this, 'validate_checkbox_settings' ) );
		register_setting( 'mc4wp_lite_form_settings', 'mc4wp_lite_form', array( $this, 'validate_form_settings' ) );

		// store whether this plugin has the BWS captcha plugin running (https://wordpress.org/plugins/captcha/)
		$this->has_captcha_plugin = function_exists( 'cptch_display_captcha_custom' );

		$this->upgrade();
	}

	/**
	 * Listen to various mc4wp actions
	 */
	private function listen() {
		// did the user click on upgrade to pro link?
		if( isset( $_GET['page'] ) && $_GET['page'] === 'mailchimp-for-wp-upgrade' && false === headers_sent() ) {
			wp_redirect( 'https://mc4wp.com/#utm_source=lite-plugin&utm_medium=link&utm_campaign=menu-upgrade-link' );
			exit;
		}
	}

	/**
	 * Set which Quicktag buttons should appear in the form mark-up editor
	 *
	 * @param array $settings
	 * @param string $editor_id
	 * @return array
	 */
	public function set_quicktags_buttons( $settings, $editor_id = '' )
	{
		if( $editor_id !== 'mc4wpformmarkup' ) {
			return $settings;
		}

		$settings['buttons'] = 'strong,em,link,img,ul,li,close';

		return $settings;
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @param       $file
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $file ) {
		if( $file !== $this->plugin_file ) {
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
	 * @param string $file
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $links, $file ) {
		if( $file !== $this->plugin_file ) {
			return $links;
		}

		$links[] = '<a href="https://wordpress.org/plugins/mailchimp-for-wp/faq/">FAQ</a>';
		$links[] = '<a href="https://mc4wp.com/#utm_source=lite-plugin&utm_medium=link&utm_campaign=plugins-upgrade-link">' . __( 'Upgrade to MailChimp for WordPress Pro', 'mailchimp-for-wp' ) . '</a>';
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
			array(
				'title' => __( 'MailChimp API Settings', 'mailchimp-for-wp' ),
				'text' => __( 'MailChimp', 'mailchimp-for-wp' ),
				'slug' => '',
				'callback' => array( $this, 'show_api_settings' ),
			),
			array(
				'title' => __( 'Checkbox Settings', 'mailchimp-for-wp' ),
				'text' => __( 'Checkboxes', 'mailchimp-for-wp' ),
				'slug' => 'checkbox-settings',
				'callback' => array( $this, 'show_checkbox_settings' ),
			),
			array(
				'title' => __( 'Form Settings', 'mailchimp-for-wp' ),
				'text' => __( 'Forms', 'mailchimp-for-wp' ),
				'slug' => 'form-settings',
				'callback' => array( $this, 'show_form_settings' ) ),
			array(
				'title' => __( 'Upgrade to Pro', 'mailchimp-for-wp' ),
				'text' => '<span style="line-height: 20px;"><span class="dashicons dashicons-external"></span> ' .__( 'Upgrade to Pro', 'mailchimp-for-wp' ),
				'slug' => 'upgrade',
				'callback' => array( $this, 'redirect_to_pro' ),
			),
		);

		$menu_items = apply_filters( 'mc4wp_menu_items', $menu_items );

		// add top menu item
		add_menu_page( 'MailChimp for WP Lite', 'MailChimp for WP', $required_cap, 'mailchimp-for-wp', array($this, 'show_api_settings'), MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png' );

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
	public function validate_settings( $settings ) {

		if( isset( $settings['api_key'] ) ) {
			$settings['api_key'] = sanitize_text_field( $settings['api_key'] );
		}

		return $settings;
	}

	/**
	* Validates the Form settings
	*
	* @param array $settings
	* @return array
	*/
	public function validate_form_settings( $settings ) {

		// If settings is malformed, just store an empty array.
		if( ! is_array( $settings ) ) {
			return array();
		}

		// Loop through new settings
		foreach( $settings as $key => $value ) {

			// sanitize text fields
			if( substr( $key, 0, 5 ) === 'text_' ) {
				$settings[ $key ] = strip_tags( trim( $value ), '<a><b><strong><em><br><i><u><pre><script><abbr><strike>' );
				continue;
			}

			switch( $key ) {

				// sanitize markup textarea
				case 'markup' :
					$settings[ $key ] = preg_replace( '/<\/?form(.|\s)*?>/i', '', $value );
					break;

				// sanitize select
				case 'css':
					$settings[ $key ] = sanitize_text_field( $value );
					break;

				// sanitize radio & checkbox inputs
				case 'double_optin':
				case 'hide_after_success':
					$settings[ $key ] = ( $value == 1 )  ? 1 : 0;
					break;
			}

		}

		return $settings;
	}

	/**
	 * Validates the Checkbox settings
	 *
	 * @param array $settings
	 * @return array
	 */
	public function validate_checkbox_settings( $settings ) {

		// If settings is malformed, just store an empty array.
		if( ! is_array( $settings ) ) {
			return array();
		}

		// Loop through new settings
		foreach( $settings as $key => $value ) {

			switch( $key ) {

				case 'lists':
					if( ! is_array( $value ) ) {
						$settings[ $key ] = array();
					} else {
						foreach( $settings[ $key ] as $list_key => $list_value ) {
							$settings[ $key ][$list_key] = sanitize_text_field( $list_value );
						}
					}
					break;

				// sanitize text inputs
				case 'label' :
					$settings[ $key ] = strip_tags( trim( $value ), '<a><b><strong><em><br><i><u><pre><script><abbr><strike>' );
					break;

				// sanitize radio & checkbox inputs
				case 'double_optin':
				case 'show_at_comment_form':
				case 'show_at_registration_form':
				case 'precheck':
				case 'css':
					$settings[ $key ] = ( $value == 1 )  ? 1 : 0;
					break;
			}

		}

		return $settings;
	}

	/**
	 * Load scripts and stylesheet on MailChimp for WP Admin pages
	 * @return bool
	*/
	public function load_css_and_js() {
		// only load asset files on the MailChimp for WordPress settings pages
		if( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'mailchimp-for-wp' ) !== 0 ) {
			return false;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$mailchimp = new MC4WP_MailChimp();

		// css
		wp_enqueue_style( 'mc4wp-admin-css', MC4WP_LITE_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css' );

		// js
		wp_register_script( 'mc4wp-beautifyhtml', MC4WP_LITE_PLUGIN_URL . 'assets/js/third-party/beautify-html'. $suffix .'.js', array( 'jquery' ), MC4WP_LITE_VERSION, true );
		wp_register_script( 'mc4wp-admin', MC4WP_LITE_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery', 'quicktags' ), MC4WP_LITE_VERSION, true );
		wp_enqueue_script( array( 'jquery', 'mc4wp-beautifyhtml', 'mc4wp-admin' ) );
		wp_localize_script( 'mc4wp-admin', 'mc4wp',
			array(
				'hasCaptchaPlugin' => $this->has_captcha_plugin,
				'strings' => array(
					'proOnlyNotice' => __( 'This option is only available in MailChimp for WordPress Pro.', 'mailchimp-for-wp' ),
					'fieldWizard' => array(
						'proOnly' => __( '(PRO ONLY)', 'mailchimp-for-wp' ),
						'buttonText' => __( 'Button text', 'mailchimp-for-wp' ),
						'initialValue' => __( 'Initial value', 'mailchimp-for-wp' ),
						'optional' => __( '(optional)', 'mailchimp-for-wp' ),
						'labelFor' => __( 'Label for', 'mailchimp-for-wp' ),
						'orLeaveEmpty' => __( '(or leave empty)', 'mailchimp-for-wp' ),
						'subscribe' => __( 'Subscribe', 'mailchimp-for-wp' )
					)
				),
				'mailchimpLists' => $mailchimp->get_lists()
			)
		);

		return true;
	}

	/**
	 * Returns available checkbox integrations
	 *
	 * @return array
	 */
	public function get_checkbox_compatible_plugins()
	{
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
	* Redirects to the premium version of MailChimp for WordPress (uses JS)
	*/
	public function redirect_to_pro()
	{
		?><script type="text/javascript">window.location.replace('https://mc4wp.com/#utm_source=lite-plugin&utm_medium=link&utm_campaign=menu-upgrade-link'); </script><?php
	}

	/**
	* Show the API settings page
	*/
	public function show_api_settings()
	{
		$opts = mc4wp_get_options( 'general' );
		$connected = ( mc4wp_get_api()->is_connected() );

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

		require MC4WP_LITE_PLUGIN_DIR . 'includes/views/api-settings.php';
	}

	/**
	* Show the Checkbox settings page
	*/
	public function show_checkbox_settings()
	{
		$mailchimp = new MC4WP_MailChimp();
		$opts = mc4wp_get_options( 'checkbox' );
		$lists = $mailchimp->get_lists();
		require MC4WP_LITE_PLUGIN_DIR . 'includes/views/checkbox-settings.php';
	}

	/**
	* Show the forms settings page
	*/
	public function show_form_settings()
	{
		$opts = mc4wp_get_options( 'form' );
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists();

		// create array of missing form fields
		$missing_form_fields = array();

		// check if form contains EMAIL field
		$search = preg_match( '/<(input|textarea)(?=[^>]*name="EMAIL")[^>]*>/i', $opts['markup'] );
		if( ! $search) {
			$missing_form_fields[] = sprintf( __( 'An EMAIL field. Example: <code>%s</code>', 'mailchimp-for-wp' ), '&lt;input type="email" name="EMAIL" /&gt;' );
		}

		// check if form contains submit button
		$search = preg_match( '/<(input|button)(?=[^>]*type="submit")[^>]*>/i', $opts['markup'] );
		if( ! $search ) {
			$missing_form_fields[] = sprintf( __( 'A submit button. Example: <code>%s</code>', 'mailchimp-for-wp' ), '&lt;input type="submit" value="'. __( 'Sign Up', 'mailchimp-for-wp' ) .'" /&gt;' );
		}

		// loop through selected list ids
		if( isset( $opts['lists'] ) && is_array( $opts['lists'] ) ) {

			foreach( $opts['lists'] as $list_id ) {

				// get list object
				$list = $mailchimp->get_list( $list_id );
				if( ! is_object( $list ) ) {
					continue;
				}

				// loop through merge vars of this list
				foreach( $list->merge_vars as $merge_var ) {

					// if field is required, make sure it's in the form mark-up
					if( ! $merge_var->req || $merge_var->tag === 'EMAIL' ) {
						continue;
					}

					// search for field tag in form mark-up using 'name="FIELD_NAME' without closing " because of array fields
					$search = stristr( $opts['markup'], 'name="'. $merge_var->tag );
					if( false === $search ) {
						$missing_form_fields[] = sprintf( __( 'A \'%s\' field', 'mailchimp-for-wp' ), $merge_var->tag );
					}

				}

			}
		}

		require MC4WP_LITE_PLUGIN_DIR . 'includes/views/form-settings.php';
	}

}