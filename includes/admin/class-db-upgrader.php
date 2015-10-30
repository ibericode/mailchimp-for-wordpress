<?php

class MC4WP_DB_Upgrader {

	/**
	 * @var int
	 */
	protected $database_version = 0;

	/**
	 * @var
	 */
	protected $code_version = 0;

	/**
	 * @param string $code_version The version we're upgrading to
	 * @param string $database_version The version the current database data is at
	 */
	public function __construct( $code_version, $database_version ) {
		$this->database_version = $database_version;
		$this->code_version = $code_version;
		$this->installing = ( $database_version === 0 );
	}

	/**
	 * Run the various upgrade routines, all the way up to the latest version
	 */
	public function run() {
		define( 'MC4WP_DOING_UPGRADE', true );

		// upgrade to 2.3
		if( ! $this->installing && version_compare( $this->database_version, '2.3', '<' ) ) {
			$this->change_success_message_key();
		}

		// upgrade to 2.3.18
		if( ! $this->installing && version_compare( $this->database_version, '2.3.18', '<' ) ) {
			$this->reschedule_usage_tracking();
		}

		// update code version
		update_option( 'mc4wp_lite_version', MC4WP_LITE_VERSION );
	}

	/**
	 * Reschedule usage tracking (using the new interval)
	 */
	protected function reschedule_usage_tracking() {

		$options = get_option( 'mc4wp_lite', array() );
		if( isset( $options['allow_usage_tracking'] ) && $options['allow_usage_tracking'] ) {
			$usage_tracking = MC4WP_Usage_Tracking::instance();
			$usage_tracking->disable();
			$usage_tracking->enable();
		}
	}

	/**
	 * Update the "key" of the success message for forms
	 */
	protected function change_success_message_key() {
		$options = get_option( 'mc4wp_lite_form', array() );
		if( isset( $options['text_success'] ) ) {
			$options['text_subscribed'] = $options['text_success'];
			unset( $options['text_success'] );
		}

		update_option( 'mc4wp_lite_form',$options );
	}








}