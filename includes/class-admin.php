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
	private $plugin_file = '';

	public function __construct()
	{
		$this->plugin_file = plugin_basename( MC4WP_LITE_PLUGIN_FILE );

		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_css_and_js' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_plugin_settings_link' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );
		add_filter( 'quicktags_settings', array( $this, 'set_quicktags_buttons' ), 10, 2 );

		// did the user click on upgrade to pro link?
		if( isset( $_GET['page'] ) && $_GET['page'] === 'mc4wp-lite-upgrade' && false === headers_sent() ) {
			header("Location: https://dannyvankooten.com/mailchimp-for-wordpress/#utm_source=lite-plugin&utm_medium=link&utm_campaign=menu-upgrade-link");
			exit;
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
		
		// css
		wp_enqueue_style( 'mc4wp-admin-css', MC4WP_LITE_PLUGIN_URL . 'assets/css/admin.css' );

		// js
		wp_register_script( 'mc4wp-beautifyhtml', MC4WP_LITE_PLUGIN_URL . 'assets/js/beautify-html.js', array( 'jquery' ), MC4WP_LITE_VERSION, true );
		wp_register_script( 'mc4wp-admin', MC4WP_LITE_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), MC4WP_LITE_VERSION, true );
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
		$tab = 'api-settings';
		$connected = ( mc4wp_get_api()->is_connected() );

		$lists = $this->get_mailchimp_lists();
		require MC4WP_LITE_PLUGIN_DIR . 'includes/views/api-settings.php';
	}

	/**
	* Show the Checkbox settings page
	*/
	public function show_checkbox_settings()
	{
		$opts = mc4wp_get_options( 'checkbox' );
		$lists = $this->get_mailchimp_lists();

		$tab = 'checkbox-settings';
		require MC4WP_LITE_PLUGIN_DIR . 'includes/views/checkbox-settings.php';
	}

	/**
	* Show the forms settings page
	*/
	public function show_form_settings()
	{
		$opts = mc4wp_get_options( 'form' );
		$lists = $this->get_mailchimp_lists();
		$tab = 'form-settings';

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
				$list = $this->get_mailchimp_list( $list_id );
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

	/**
	* Get MailChimp lists
	* Try cache first, then try API, then try fallback cache.
	*
	* @return array
	*/
	private function get_mailchimp_lists()
	{
		$cached_lists = get_transient( 'mc4wp_mailchimp_lists' );
		$refresh_cache = ( isset( $_POST['mc4wp-renew-cache'] ) && $_POST['mc4wp-renew-cache'] == 1 );

		if( true === $refresh_cache || false === $cached_lists || empty( $cached_lists ) ) {
			// make api request for lists
			$api = mc4wp_get_api();
			$lists = array();
			$lists_data = $api->get_lists();

			if( $lists_data ) {
				
				$list_ids = array();
				foreach( $lists_data as $list ) {
					$list_ids[] = $list->id;

					$lists["{$list->id}"] = (object) array(
						'id' => $list->id,
						'name' => $list->name,
						'subscriber_count' => $list->stats->member_count,
						'merge_vars' => array(),
						'interest_groupings' => array()
					);

					// get interest groupings
					$groupings_data = $api->get_list_groupings( $list->id );
					if( $groupings_data ) {
						$lists["{$list->id}"]->interest_groupings = array_map( array( $this, 'strip_unnecessary_grouping_properties' ), $groupings_data );
					}
				}

				// get merge vars for all lists at once
				$merge_vars_data = $api->get_lists_with_merge_vars( $list_ids );
				if( $merge_vars_data ) {
					foreach( $merge_vars_data as $list ) {
						// add merge vars to list
						$lists["{$list->id}"]->merge_vars = array_map( array( $this, 'strip_unnecessary_merge_vars_properties' ), $list->merge_vars );
					}
				}

				// cache renewal triggered manually?
				if( $refresh_cache ) {
					if( false === empty( $lists ) ) {
						add_settings_error( "mc4wp", "cache-renewed", __('MailChimp cache successfully renewed.', 'mailchimp-for-wp' ), 'updated' );
					} else {
						add_settings_error( "mc4wp", "cache-renew-failed", __('Failed to renew MailChimp cache - please try again later.', 'mailchimp-for-wp' ) );
					}
				}

				// store lists in transients
				set_transient( 'mc4wp_mailchimp_lists', $lists, ( 24 * 3600 ) ); // 1 day
				set_transient( 'mc4wp_mailchimp_lists_fallback', $lists, 1209600 ); // 2 weeks
				return $lists;
			} else {
				// api request failed, get fallback data (with longer lifetime)
				$cached_lists = get_transient('mc4wp_mailchimp_lists_fallback');

				if( ! $cached_lists ) { 
					return array(); 
				}
			}
			
		}

		return $cached_lists;
	}

	/**
	 * @param $list_id
	 *
	 * @return bool
	 */
	private function get_mailchimp_list( $list_id ) {
		$lists = $this->get_mailchimp_lists();

		foreach( $lists as $list ) {
			if( $list->id === $list_id ) {
				return $list;
			}
		}

		return false;
	}

	/**
	* Build the group array object which will be stored in cache
	* @param object $group
	* @return object
	*/ 
	public function strip_unnecessary_group_properties( $group ) {
		return (object) array(
			'name' => $group->name
		);
	}

	/**
	* Build the groupings array object which will be stored in cache
	* @param object $grouping
	* @return object
	*/ 
	public function strip_unnecessary_grouping_properties( $grouping )
	{
		return (object) array(
			'id' => $grouping->id,
			'name' => $grouping->name,
			'groups' => array_map( array( $this, 'strip_unnecessary_group_properties' ), $grouping->groups ),
			'form_field' => $grouping->form_field
		);
	}

	/**
	* Build the merge_var array object which will be stored in cache
	* @param object $merge_var
	* @return object
	*/ 
	public function strip_unnecessary_merge_vars_properties( $merge_var )
	{
		$array = array(
			'name' => $merge_var->name,
			'field_type' => $merge_var->field_type,
			'req' => $merge_var->req,
			'tag' => $merge_var->tag
		);

		if ( isset( $merge_var->choices ) ) {
			$array["choices"] = $merge_var->choices;
		}

		return (object) $array;
	}

}