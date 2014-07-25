<?php

if( ! defined( "MC4WP_LITE_VERSION" ) ) {
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
	private $plugin_file = 'mailchimp-for-wp/mailchimp-for-wp.php';

	public function __construct()
	{
		$this->setup_hooks();

		// did the user click on upgrade to pro link?
		if( isset( $_GET['page'] ) && $_GET['page'] === 'mc4wp-lite-upgrade' && false === headers_sent() ) {
			header("Location: https://dannyvankooten.com/mailchimp-for-wordpress/#utm_source=lite-plugin&utm_medium=link&utm_campaign=menu-upgrade-link");
			exit;
		}

	}

	/**
	 * Registers all hooks
	 */
	private function setup_hooks() {

		global $pagenow;

		// Actions used throughout WP Admin
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_css_and_js' ) );

		// Hooks for Plugins overview
		if( isset( $pagenow ) && $pagenow === 'plugins.php' ) {
			$this->plugin_file = plugin_basename( MC4WP_LITE_PLUGIN_FILE );

			add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );
		}

		// Hooks for Form settings page
		if( isset( $_GET['page'] ) && $_GET['page'] === 'mc4wp-lite-form-settings' ) {
			add_filter( 'quicktags_settings', array( $this, 'set_quicktags_buttons' ), 10, 2 );
		}

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
		register_setting( 'mc4wp_lite_checkbox_settings', 'mc4wp_lite_checkbox');
		register_setting( 'mc4wp_lite_form_settings', 'mc4wp_lite_form', array( $this, 'validate_form_settings' ) );

		// load the plugin text domain
		load_plugin_textdomain( 'mailchimp-for-wp', false, dirname( $this->plugin_file ) . '/languages/' );

		// store whether this plugin has the BWS captcha plugin running (http://wordpress.org/plugins/captcha/)
		$this->has_captcha_plugin = function_exists( 'cptch_display_captcha_custom' );
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
	* @param array $links
	* @return array
	*/
	public function add_plugin_settings_link( $links, $file )
	{
		if( $file !== $this->plugin_file ) {
			return $links;
		}

		 $settings_link = '<a href="admin.php?page=mc4wp-lite">'. __( 'Settings' ) . '</a>';
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

		$links[] = '<a href="http://wordpress.org/plugins/mailchimp-for-wp/faq/">FAQ</a>';
		$links[] = '<a href="https://dannyvankooten.com/mailchimp-for-wordpress/#utm_source=lite-plugin&utm_medium=link&utm_campaign=plugins-upgrade-link">' . __( 'Upgrade to Pro', 'mailchimp-for-wp' ) . '</a>';
		return $links;
	}

	/**
	* Register the setting pages and their menu items
 	*/
	public function build_menu()
	{
		$required_cap = apply_filters( 'mc4wp_settings_cap', 'manage_options' );
		add_menu_page( 'MailChimp for WP Lite', 'MailChimp for WP', $required_cap, 'mc4wp-lite', array($this, 'show_api_settings'), MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png' );
		add_submenu_page( 'mc4wp-lite', 'API Settings - MailChimp for WP Lite', __( 'MailChimp Settings', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-lite', array( $this, 'show_api_settings' ) );
		add_submenu_page( 'mc4wp-lite', 'Checkbox Settings - MailChimp for WP Lite', __( 'Checkboxes', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-lite-checkbox-settings', array($this, 'show_checkbox_settings' ) );
		add_submenu_page( 'mc4wp-lite', 'Form Settings - MailChimp for WP Lite', __( 'Forms', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-lite-form-settings', array( $this, 'show_form_settings' ) );
		add_submenu_page( 'mc4wp-lite', 'Upgrade to Pro - MailChimp for WP Lite', __( 'Upgrade to Pro', 'mailchimp-for-wp' ), $required_cap, 'mc4wp-lite-upgrade', array( $this, 'redirect_to_pro' ) );
	}


	/**
	* Validates the General settings
	*
	* @param array $settings
	* @return array
	*/
	public function validate_settings( $settings ) {

		if( isset( $settings['api_key'] ) ) {
			$settings['api_key'] = trim( strip_tags( $settings['api_key'] ) );
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

		if( isset( $settings['markup'] ) ) {

			// strip form tags (to prevent people from adding them)
			$settings['markup'] = preg_replace( '/<\/?form(.|\s)*?>/i', '', $settings['markup'] );

		}

		return $settings;
	}

	/**
	* @param string $hook
	*/
	public function load_css_and_js( $hook )
	{
		// only load asset files on the MailChimp for WordPress settings pages
		if( false === isset( $_GET['page'] ) || false === stristr( $_GET['page'], 'mc4wp-lite' ) ) {
			return; 
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// css
		wp_enqueue_style( 'mc4wp-admin-css', MC4WP_LITE_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css' );

		// js
		wp_register_script( 'mc4wp-beautifyhtml', MC4WP_LITE_PLUGIN_URL . 'assets/js/beautify-html'. $suffix .'.js', array( 'jquery' ), MC4WP_LITE_VERSION, true );
		wp_register_script( 'mc4wp-admin', MC4WP_LITE_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery' ), MC4WP_LITE_VERSION, true );
		wp_enqueue_script( array( 'jquery', 'mc4wp-beautifyhtml', 'mc4wp-admin' ) );
		wp_localize_script( 'mc4wp-admin', 'mc4wp',
			array(
				'has_captcha_plugin' => $this->has_captcha_plugin
			)
		);
	}

	/**
     * Returns available checkbox integrations
     *
     * @return array
	 */
	public function get_checkbox_compatible_plugins()
	{
		$checkbox_plugins = array(
			'comment_form' => __( "Comment form", 'mailchimp-for-wp' ),
			"registration_form" => __( "Registration form", 'mailchimp-for-wp' )
		);

		if( is_multisite() ) {
            $checkbox_plugins['multisite_form'] = __( "MultiSite forms", 'mailchimp-for-wp' );
        }

		if( class_exists("BuddyPress") ) {
            $checkbox_plugins['buddypress_form'] = __( "BuddyPress registration", 'mailchimp-for-wp' );
        }

		if( class_exists('bbPress') ) {
            $checkbox_plugins['bbpress_forms'] = "bbPress";
        }

		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
            $checkbox_plugins['_edd_checkout'] = "(PRO ONLY) Easy Digital Downloads checkout";
        }

		if ( class_exists( 'Woocommerce' ) ) {
            $checkbox_plugins['_woocommerce_checkout'] = "(PRO ONLY) WooCommerce checkout";
        }

		return $checkbox_plugins;
	}

	/**
	* Redirects to the premium version of MailChimp for WordPress (uses JS)
	*/
	public function redirect_to_pro()
	{
		?><script type="text/javascript">window.location.replace('https://dannyvankooten.com/mailchimp-for-wordpress/#utm_source=lite-plugin&utm_medium=link&utm_campaign=menu-upgrade-link'); </script><?php
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

		if ( $force_cache_refresh ) {
			if ( false === empty ( $lists ) ) {
				add_settings_error( "mc4wp", "mc4wp-cache-success", __( 'Renewed MailChimp cache.', 'mailchimp-for-wp' ), 'updated' );
			} else {
				add_settings_error( "mc4wp", "mc4wp-cache-error", __( 'Failed to renew MailChimp cache - please try again later.', 'mailchimp-for-wp' ) );
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

					// search for field tag in form mark-up
					$search = stristr( $opts['markup'], 'name="'. $merge_var->tag .'"' );
					if( false === $search ) {
						$missing_form_fields[] = sprintf( __( 'A \'%s\' field', 'mailchimp-for-wp' ), $merge_var->tag );
					}

				}

			}
		}

		require MC4WP_LITE_PLUGIN_DIR . 'includes/views/form-settings.php';
	}

}