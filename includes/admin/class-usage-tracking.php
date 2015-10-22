<?php

class MC4WP_Usage_Tracking {

	/**
	 * @var string
	 */
	protected $tracking_url = 'https://mc4wp.com/api/usage-tracking';

	/**
	 * @var MC4WP_Usage_Tracking The One True Instance
	 */
	protected static $instance;

	/**
	 * @return MC4WP_Usage_Tracking
	 */
	public static function instance() {

		if( ! self::$instance instanceof MC4WP_Usage_Tracking ) {
			self::$instance = new MC4WP_Usage_Tracking();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'mc4wp_usage_tracking', array( $this, 'track' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
	}

	/**
	 * Registers a new schedule with WP Cron
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function cron_schedules( $schedules ) {
		$schedules['weekly'] = array(
			'interval' => 7 * DAY_IN_SECONDS,
			'display' => __( 'Once Weekly' )
		);
		return $schedules;
	}

	/**
	 * Toggle tracking (clears & sets the scheduled tracking event)
	 *
	 * @param bool $enabled
	 */
	public function toggle( $enabled ) {

		if( ! $enabled ) {
			wp_clear_scheduled_hook( 'mc4wp_usage_tracking' );
			return;
		}

		wp_schedule_event( time(), 'weekly', 'mc4wp_usage_tracking' );
	}

	/**
	 * Sends the tracking request. Non-blocking.
	 *
	 * @return bool
	 */
	public function track() {
		$data = $this->get_tracking_data();

		// send non-blocking request and be done with it
		$response = wp_remote_post( $this->tracking_url, array(
				'body' => json_encode( $data ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept' => 'application/json'
				),
				'blocking' => false,
			)
		);

		return true;
	}

	/**
	 * @return array
	 */
	protected function get_tracking_data() {

		$data = array(
			// use md5 hash of home_url, we don't need/want to know the actual site url
			'site' => md5( home_url() ),
			'options' => $this->get_tracked_options(),
			'number_of_mailchimp_lists' => $this->get_mailchimp_lists_count(),
			'mc4wp_version' => $this->get_mc4wp_version(),
			'plugins' => (array) get_option( 'active_plugins', array() ),
			'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'curl_version' => $this->get_curl_version(),
			'wp_version' => $GLOBALS['wp_version'],
			'wp_language' => get_locale(),
			'server_software' => $this->get_server_software(),
			'using_https' => $this->is_site_using_https()
		);

		return $data;
	}

	/**
	 * Returns the MailChimp for WordPress version (either Lite or Pro if version < 3.0)
	 *
	 * @return string
	 */
	protected function get_mc4wp_version() {

		if( defined( 'MC4WP_VERSION' ) ) {
			return MC4WP_VERSION;
		}

		if( defined( 'MC4WP_LITE_VERSION' ) ) {
			return MC4WP_LITE_VERSION;
		}

		if( defined( 'MC4WP_PRO_VERSION' ) ) {
			return MC4WP_PRO_VERSION;
		}

		// never..
		return 0;
	}

	/**
	 * @return int
	 */
	protected function get_mailchimp_lists_count() {
		$mailchimp = new MC4WP_MailChimp();
		$lists = $mailchimp->get_lists( false, true );
		return count( $lists );
	}

	/**
	 * @return array
	 */
	public function get_tracked_options( ) {

//		$checkbox_options = mc4wp_get_options( 'checkbox' );
//		$form_options = mc4wp_get_options( 'form' );
//
//		// make sure these keys are always stripped
//		$ignored_options = array( 'api_key', 'license_key', 'lists' );
//
//		// filter options
//		$checkbox_options = array_diff_key( $checkbox_options, array_flip( $ignored_options ) );
//		$form_options = array_diff_key( $form_options, array_flip( $ignored_options ) );
//
//		// merge options
//		$options = array(
//			'checkbox' => $checkbox_options,
//			'form' => $form_options
//		);

		// @todo fix this method
		$options = array();
		return $options;
	}

	/**
	 * @return string
	 */
	protected function get_curl_version() {

		if( ! function_exists( 'curl_version' ) ) {
			return 0;
		}

		$curl_version_info = curl_version();
		return $curl_version_info['version'];
	}

	/**
	 * @return bool
	 */
	protected function is_site_using_https() {
		$site_url = site_url();
		return strpos( $site_url, 'https://' ) === 0;
	}

	/**
	 * @return string
	 */
	protected function get_server_software() {

		if( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return '';
		}

		return $_SERVER['SERVER_SOFTWARE'];
	}
}