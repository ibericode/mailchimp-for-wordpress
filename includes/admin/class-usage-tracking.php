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

		wp_schedule_event( time(), 'daily', 'mc4wp_usage_tracking' );
	}

	/**
	 * Sends the tracking request. Non-blocking.
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
	}

	/**
	 * @return array
	 */
	protected function get_tracking_data() {

		$data = array(
			'site' => home_url(),
			'options' => $this->get_tracked_options(),
			'mc4wp_version' => MC4WP_LITE_VERSION,
			'plugins' => wp_get_active_and_valid_plugins(),
			'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'curl_version' => $this->get_curl_version(),
			'wp_version' => $GLOBALS['wp_version']
		);

		return $data;
	}

	/**
	 * @return array
	 */
	public function get_tracked_options( ) {

		$checkbox_options = mc4wp_get_options( 'checkbox' );
		$form_options = mc4wp_get_options( 'form' );
		$ignored_options = array( 'api_key', 'license_key' );

		// filter options
		$checkbox_options = array_diff_key( $checkbox_options, array_flip( $ignored_options ) );
		$form_options = array_diff_key( $form_options, array_flip( $ignored_options ) );

		// merge options
		$options = array(
			'checkbox' => $checkbox_options,
			'form' => $form_options
		);

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
}