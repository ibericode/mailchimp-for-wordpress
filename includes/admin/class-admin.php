<?php

/**
* Class MC4WP_Admin
*
* @ignore
* @access private
*/
class MC4WP_Admin
{
	/**
	* @var string The relative path to the main plugin file from the plugins dir
	*/
	protected $plugin_file;

	/**
	* @var MC4WP_Admin_Messages
	*/
	protected $messages;

	/**
	* @var MC4WP_Admin_Ads
	*/
	protected $ads;

	/**
	* @var MC4WP_Admin_Tools
	*/
	protected $tools;

	/**
	* @var MC4WP_Admin_Review_Notice
	*/
	protected $review_notice;

	/**
	* Constructor
	*
	* @param MC4WP_Admin_Tools $tools
	* @param MC4WP_Admin_Messages $messages
	*/
	public function __construct(MC4WP_Admin_Tools $tools, MC4WP_Admin_Messages $messages)
	{
		$this->tools         = $tools;
		$this->messages      = $messages;
		$this->plugin_file   = plugin_basename(MC4WP_PLUGIN_FILE);
		$this->ads           = new MC4WP_Admin_Ads();
		$this->review_notice = new MC4WP_Admin_Review_Notice($tools);
	}

	/**
	* Registers all hooks
	*/
	public function add_hooks()
	{

		// Actions used globally throughout WP Admin
		add_action('admin_menu', array( $this, 'build_menu' ));
		add_action('admin_init', array( $this, 'initialize' ));

		add_action('current_screen', array( $this, 'customize_admin_texts' ));
		add_action('wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ));
		add_action('mc4wp_admin_empty_lists_cache', array( $this, 'renew_lists_cache' ));
		add_action('mc4wp_admin_empty_debug_log', array( $this, 'empty_debug_log' ));

		add_action('admin_notices', array( $this, 'show_api_key_notice' ));
		add_action('mc4wp_admin_dismiss_api_key_notice', array( $this, 'dismiss_api_key_notice' ));
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_assets' ));

		$this->ads->add_hooks();
		$this->messages->add_hooks();
		$this->review_notice->add_hooks();
	}

	/**
	* Initializes various stuff used in WP Admin
	*
	* - Registers settings
	*/
	public function initialize()
	{

		// register settings
		register_setting('mc4wp_settings', 'mc4wp', array( $this, 'save_general_settings' ));

		// Load upgrader
		$this->init_upgrade_routines();

		// listen for custom actions
		$this->listen_for_actions();
	}


	/**
	* Listen for `_mc4wp_action` requests
	*/
	public function listen_for_actions()
	{
		// do nothing if _mc4wp_action was not in the request parameters
		if (! isset($_REQUEST['_mc4wp_action'])) {
			return;
		}

		// check if user is authorized
		if (! $this->tools->is_user_authorized()) {
			return;
		}

		// verify nonce
		if (! isset($_REQUEST['_wpnonce']) || false === wp_verify_nonce($_REQUEST['_wpnonce'], '_mc4wp_action')) {
			wp_nonce_ays('_mc4wp_action');
			exit;
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
		do_action('mc4wp_admin_' . $action);

		// redirect back to where we came from (to prevent double submit)
		if (isset($_POST['_redirect_to'])) {
			$redirect_url = $_POST['_redirect_to'];
		} elseif (isset($_GET['_redirect_to'])) {
			$redirect_url = $_GET['_redirect_to'];
		} else {
			$redirect_url = remove_query_arg('_mc4wp_action');
		}

		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	* Register dashboard widgets
	*/
	public function register_dashboard_widgets()
	{
		if (! $this->tools->is_user_authorized()) {
			return;
		}

		/**
		* Setup dashboard widget, users are authorized by now.
		*
		* Use this hook to register your own dashboard widgets for users with the required capability.
		*
		* @since 3.0
		* @ignore
		*/
		do_action('mc4wp_dashboard_setup');
	}

	/**
	* Upgrade routine
	*/
	private function init_upgrade_routines()
	{
		// upgrade routine for upgrade routine....
		$previous_version = get_option('mc4wp_lite_version', 0);
		if ($previous_version) {
			delete_option('mc4wp_lite_version');
			update_option('mc4wp_version', $previous_version);
		}

		$previous_version = get_option('mc4wp_version', 0);

		// Ran upgrade routines before?
		if (empty($previous_version)) {
			update_option('mc4wp_version', MC4WP_VERSION);

			// if we have at least one form, we're going to run upgrade routine for v3 => v4 anyway.
			$posts = get_posts(
				array(
					'post_type'   => 'mc4wp-form',
					'posts_per_page' => 1,
				)
			);
			if (empty($posts)) {
				return;
			}

			$previous_version = '3.9';
		}

		// This means we're good!
		if (version_compare($previous_version, MC4WP_VERSION, '>=')) {
			return;
		}

		define('MC4WP_DOING_UPGRADE', true);
		$upgrade_routines = new MC4WP_Upgrade_Routines($previous_version, MC4WP_VERSION, __DIR__ . '/migrations');
		$upgrade_routines->run();
		update_option('mc4wp_version', MC4WP_VERSION);
	}

	/**
	* Renew Mailchimp lists cache
	*/
	public function renew_lists_cache()
	{
		// try getting new lists to fill cache again
		$mailchimp = new MC4WP_MailChimp();
		$lists     = $mailchimp->refresh_lists();

		if (! empty($lists)) {
			$this->messages->flash(esc_html__('Success! The cached configuration for your Mailchimp lists has been renewed.', 'mailchimp-for-wp'));
		}
	}

	/**
	* Customize texts throughout WP Admin
	*/
	public function customize_admin_texts()
	{
		$texts = new MC4WP_Admin_Texts($this->plugin_file);
		$texts->add_hooks();
	}

	/**
	* Validates the General settings
	* @param array $settings
	* @return array
	*/
	public function save_general_settings(array $settings)
	{
		$current = mc4wp_get_options();

		// merge with current settings to allow passing partial arrays to this method
		$settings = array_merge($current, $settings);

		// Make sure not to use obfuscated key
		if (strpos($settings['api_key'], '*') !== false) {
			$settings['api_key'] = $current['api_key'];
		}

		// Sanitize API key
		$settings['api_key'] = sanitize_text_field($settings['api_key']);

		// if API key changed, empty Mailchimp cache
		if ($settings['api_key'] !== $current['api_key']) {
			delete_transient('mc4wp_mailchimp_lists');
		}

		/**
		* Runs right before general settings are saved.
		*
		* @param array $settings The updated settings array
		* @param array $current The old settings array
		*/
		do_action('mc4wp_save_settings', $settings, $current);

		return $settings;
	}

	/**
	* Load scripts and stylesheet on Mailchimp for WP Admin pages
	*/
	public function enqueue_assets()
	{
		if (! $this->tools->on_plugin_page()) {
			return;
		}

		$opts      = mc4wp_get_options();
		$page      = $this->tools->get_plugin_page();
		$mailchimp = new MC4WP_MailChimp();

		// css
		wp_register_style('mc4wp-admin', mc4wp_plugin_url('assets/css/admin.css'), array(), MC4WP_VERSION);
		wp_enqueue_style('mc4wp-admin');

		// js
		wp_register_script('mc4wp-admin', mc4wp_plugin_url('assets/js/admin.js'), array(), MC4WP_VERSION, true);
		wp_enqueue_script('mc4wp-admin');
		$connected       = ! empty($opts['api_key']);
		$mailchimp_lists = $connected ? $mailchimp->get_lists() : array();
		wp_localize_script(
			'mc4wp-admin',
			'mc4wp_vars',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('mc4wp-ajax'),
				'mailchimp' => array(
					'api_connected' => $connected,
					'lists'         => $mailchimp_lists,
				),
				'countries' => MC4WP_Tools::get_countries(),
				'i18n'      => array(
					'invalid_api_key'                => __('The given value does not look like a valid Mailchimp API key.', 'mailchimp-for-wp'),
					'pro_only'                       => __('This is a premium feature. Please upgrade to Mailchimp for WordPress Premium to be able to use it.', 'mailchimp-for-wp'),
					'renew_mailchimp_lists'          => __('Renew Mailchimp audiences', 'mailchimp-for-wp'),
					'fetching_mailchimp_lists'       => __('Fetching Mailchimp audiences', 'mailchimp-for-wp'),
					'fetching_mailchimp_lists_done'  => __('Done! Mailchimp audiences renewed.', 'mailchimp-for-wp'),
					'fetching_mailchimp_lists_error' => __('Failed to renew your audiences. An error occured.', 'mailchimp-for-wp'),
				),
			)
		);

		/**
		* Hook to enqueue your own custom assets on the Mailchimp for WordPress setting pages.
		*
		* @since 3.0
		*
		* @param string $suffix
		* @param string $page
		*/
		do_action('mc4wp_admin_enqueue_assets', '', $page);
	}



	/**
	* Register the setting pages and their menu items
	*/
	public function build_menu()
	{
		$required_cap = $this->tools->get_required_capability();

		$menu_items = array(
			array(
				'title'    => esc_html__('Mailchimp API Settings', 'mailchimp-for-wp'),
				'text'     => 'Mailchimp',
				'slug'     => '',
				'callback' => array( $this, 'show_generals_setting_page' ),
				'position' => 0,
			),
			array(
				'title'    => esc_html__('Other Settings', 'mailchimp-for-wp'),
				'text'     => esc_html__('Other', 'mailchimp-for-wp'),
				'slug'     => 'other',
				'callback' => array( $this, 'show_other_setting_page' ),
				'position' => 90,
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
		$menu_items = (array) apply_filters('mc4wp_admin_menu_items', $menu_items);

		// add top menu item
		$icon = file_get_contents(MC4WP_PLUGIN_DIR . '/assets/img/icon.svg');
		add_menu_page('Mailchimp for WP', 'MC4WP', $required_cap, 'mailchimp-for-wp', array( $this, 'show_generals_setting_page' ), 'data:image/svg+xml;base64,' . base64_encode($icon), '99.68491');

		// sort submenu items by 'position'
		usort($menu_items, array( $this, 'sort_menu_items_by_position' ));

		// add sub-menu items
		foreach ($menu_items as $item) {
			$this->add_menu_item($item);
		}
	}

	/**
	* @param array $item
	*/
	public function add_menu_item(array $item)
	{

		// generate menu slug
		$slug = 'mailchimp-for-wp';
		if (! empty($item['slug'])) {
			$slug .= '-' . $item['slug'];
		}

		// provide some defaults
		$parent_slug = ! empty($item['parent_slug']) ? $item['parent_slug'] : 'mailchimp-for-wp';
		$capability  = ! empty($item['capability']) ? $item['capability'] : $this->tools->get_required_capability();

		// register page
		$hook = add_submenu_page($parent_slug, $item['title'] . ' - Mailchimp for WordPress', $item['text'], $capability, $slug, $item['callback']);

		// register callback for loading this page, if given
		if (array_key_exists('load_callback', $item)) {
			add_action('load-' . $hook, $item['load_callback']);
		}
	}

	/**
	* Show the API Settings page
	*/
	public function show_generals_setting_page()
	{
		$opts      = mc4wp_get_options();
		$api_key   = mc4wp_get_api_key();
		$lists     = array();
		$connected = ! empty($api_key);

		if ($connected) {
			try {
				$connected = $this->get_api()->is_connected();
				$mailchimp = new MC4WP_MailChimp();
				$lists     = $mailchimp->get_lists();
			} catch (MC4WP_API_Connection_Exception $e) {
				$message = sprintf('<strong>%s</strong> %s %s ', esc_html__('Error connecting to Mailchimp:', 'mailchimp-for-wp'), $e->getCode(), $e->getMessage());

				if (is_object($e->response_data) && ! empty($e->response_data->ref_no)) {
					$message .= '<br />' . sprintf(esc_html__('Looks like your server is blocked by Mailchimp\'s firewall. Please contact Mailchimp support and include the following reference number: %s', 'mailchimp-for-wp'), $e->response_data->ref_no);
				}

				$message .= '<br /><br />' . sprintf('<a href="%s">' . esc_html__('Here\'s some info on solving common connectivity issues.', 'mailchimp-for-wp') . '</a>', 'https://www.mc4wp.com/kb/solving-connectivity-issues/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=settings-notice');

				$this->messages->flash($message, 'error');
				$connected = false;
			} catch (MC4WP_API_Exception $e) {
				$message = sprintf('<strong>%s</strong><br /> %s', esc_html__('Mailchimp returned the following error:', 'mailchimp-for-wp'), nl2br((string) $e));
				$this->messages->flash($message, 'error');
				$connected = false;
			}
		}

		$obfuscated_api_key = mc4wp_obfuscate_string($api_key);
		require MC4WP_PLUGIN_DIR . '/includes/views/general-settings.php';
	}

	/**
	* Show the Other Settings page
	*/
	public function show_other_setting_page()
	{
		$opts       = mc4wp_get_options();
		$log        = $this->get_log();
		$log_reader = new MC4WP_Debug_Log_Reader($log->file);
		require MC4WP_PLUGIN_DIR . '/includes/views/other-settings.php';
	}

	/**
	* @param $a
	* @param $b
	*
	* @return int
	*/
	public function sort_menu_items_by_position($a, $b)
	{
		$pos_a = isset($a['position']) ? $a['position'] : 80;
		$pos_b = isset($b['position']) ? $b['position'] : 90;
		return $pos_a < $pos_b ? -1 : 1;
	}

	/**
	* Empties the log file
	*/
	public function empty_debug_log()
	{
		$log = $this->get_log();
		file_put_contents($log->file, '');

		$this->messages->flash(esc_html__('Log successfully emptied.', 'mailchimp-for-wp'));
	}

	/**
	* Shows a notice when API key is not set.
	*/
	public function show_api_key_notice()
	{

		// don't show if on settings page already
		if ($this->tools->on_plugin_page('')) {
			return;
		}

		// only show to user with proper permissions
		if (! $this->tools->is_user_authorized()) {
			return;
		}

		// don't show if dismissed
		if (get_transient('mc4wp_api_key_notice_dismissed')) {
			return;
		}

		// don't show if api key is set already
		$api_key = mc4wp_get_api_key();
		if (! empty($api_key)) {
			return;
		}

		echo '<div class="notice notice-warning mc4wp-is-dismissible">';
		echo '<p>', sprintf(wp_kses(__('To get started with Mailchimp for WordPress, please <a href="%s">enter your Mailchimp API key on the settings page of the plugin</a>.', 'mailchimp-for-wp'), array( 'a' => array( 'href' => array() ) )), admin_url('admin.php?page=mailchimp-for-wp')), '</p>';
		echo '<form method="post"><input type="hidden" name="_mc4wp_action" value="dismiss_api_key_notice" /><button type="submit" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></form>';
		echo '</div>';
	}

	/**
	* Dismisses the API key notice for 1 week
	*/
	public function dismiss_api_key_notice()
	{
		set_transient('mc4wp_api_key_notice_dismissed', 1, 3600 * 24 * 7);
	}

	/**
	* @return MC4WP_Debug_Log
	*/
	protected function get_log()
	{
		return mc4wp('log');
	}

	/**
	* @return MC4WP_API_V3
	*/
	protected function get_api()
	{
		return mc4wp('api');
	}
}
