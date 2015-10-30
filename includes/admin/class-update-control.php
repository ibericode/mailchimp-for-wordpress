<?php

class MC4WP_Update_Control {

	/**
	 * @const string
	 */
	const CAPABILITY = 'install_plugins';

	/**
	 * @const string
	 */
	const OPTION_SHOW_NOTICE = 'mc4wp_show_major_updates_notice';

	/**
	 * @const string
	 */
	const OPTION_DISMISS_NOTICE = 'mc4wp_dismiss_major_updates_notice';

	/**
	 * @const string
	 */
	const OPTION_ENABLE_MAJOR_UPDATES = 'mc4wp_enable_major_updates';

	/**
	 * @var string
	 */
	protected $plugin_file = '';

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// hide major plugin updates for everyone
		global $pagenow;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'hide_major_plugin_updates' ) );
		add_action( 'init', array( $this, 'listen' ) );

		if( $pagenow === 'plugins.php' || ( ! empty( $_GET['page'] ) && stripos( $_GET['page'], 'mailchimp-for-wp' ) !== false ) ) {
			add_action( 'admin_notices', array( $this, 'show_update_optin' ) );
		}
	}

	/**
	 * Listen for actions
	 */
	public function listen() {

		// only show to users with required capability
		if( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		if( isset( $_GET[ self::OPTION_DISMISS_NOTICE ] ) ) {
			$this->dismiss_notice();
		}


		if( isset( $_GET[ self::OPTION_ENABLE_MAJOR_UPDATES ] ) ) {
			$this->enable_major_updates();
		}

	}

	/**
	 * Prevents v3.x updates from showing
	 *
	 */
	public function hide_major_plugin_updates( $data ) {

		// fake set new version to 3.0 (for testing)
		// @todo always comment this out
//		$data->response[ $this->plugin_file ] = $data->no_update[ $this->plugin_file ];
//		$data->response[ $this->plugin_file ]->new_version = "3.0.0";

		// don't act if there's no update to act upon
		if( empty( $data->response[ $this->plugin_file ]->new_version ) ) {
			return $data;
		}

		$wordpress_org_data = $data->response[ $this->plugin_file ];

		// is there a major update for this plugin?
		if( ! version_compare( $wordpress_org_data->new_version, '3.0.0', '>=' ) ) {
			return $data;
		}

		// did user opt-in to 3.0? if so, show the update.
		$opted_in = get_option( self::OPTION_ENABLE_MAJOR_UPDATES, false );
		if( $opted_in ) {
			return $data;
		}

		// user did not opt-in

		// set a flag to start showing "update to 3.x" notice
		update_option( self::OPTION_SHOW_NOTICE, 1 );

		// get latest minor version and download link (from aws bucket)
		$minor_update_data = $this->get_latest_minor_update();

		// if something in the custom update check failed, just unset the data.
		if ( ! is_object( $minor_update_data ) ) {
			unset( $data->response[ $this->plugin_file ] );
			return $data;
		}

		// return modified updates data
		$data->response[ $this->plugin_file ] = $this->merge_update_data( $wordpress_org_data, $minor_update_data );

		return $data;
	}

	/**
	 *
	 *
	 * @return array|mixed|object
	 */
	protected function get_latest_minor_update() {

		// try to get from transient first
		$cached = get_transient( 'mc4wp_minor_update_info' );
		if( is_object( $cached ) ) {
			return $cached;
		}

		// no? query it then.
		$response = wp_remote_get( 'https://s3.amazonaws.com/ibericode/mailchimp-for-wp-update-info-2.x.json' );
		$body     = wp_remote_retrieve_body( $response );

		if( empty( $body ) ) {
			return false;
		}

		$data = json_decode( $body );
		set_transient( 'mc4wp_minor_update_info', $data, 7200 ); // cache for 2 hours

		return $data;
	}

	/**
	 * @param object $wordpress_org_data
	 * @param object $custom_data
	 *
	 * @return object
	 */
	protected function merge_update_data( $wordpress_org_data, $custom_data ) {
		return (object) array_merge(
			(array) $wordpress_org_data,
			(array) $custom_data
		);
	}

	/**
	 *  Dismiss notice for a week
	 */
	public function dismiss_notice() {
		set_transient( self::OPTION_DISMISS_NOTICE, 1, WEEK_IN_SECONDS );
	}


	/**
	 * Maybe enable major updates
	 */
	public function enable_major_updates() {

		// update option
		update_option( self::OPTION_ENABLE_MAJOR_UPDATES, 1 );

		// delete site transient so wp core will fetch latest version
		delete_site_transient( 'update_plugins' );

		// redirect to updates page
		wp_safe_redirect( admin_url( 'update-core.php' ) );
		exit;
	}

	/**
	 * Show update opt-in
	 */
	public function show_update_optin() {

		global $pagenow;

		// don't show if flag is not set
		if( ! get_option( self::OPTION_SHOW_NOTICE, false ) ) {
			return;
		}

		// stop showing if opted-in already
		if( get_option( self::OPTION_ENABLE_MAJOR_UPDATES, false ) ) {
			return;
		}

		// only show to users with required capability
		if( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		// if on plugins page and dismissed, do not show
		if( $pagenow === 'plugins.php' && get_transient( self::OPTION_DISMISS_NOTICE ) ) {
			return;
		}

		// show!
		echo '<div class="notice is-dismissible updated">';

		echo '<h4>' . __( 'MailChimp for WordPress 3.0 is available for you', 'mailchimp-for-wp' ) . '</h4>';
		echo '<p>' . __( 'Version 3.0 is here, containing so many improvements it would not fit in this notice even if we tried.', 'mailchimp-for-wp' ) . '</p>';
		echo '<p>';
		echo __( 'However, we changed a few minor things and want to make sure you are aware of the changes before proceeding with the update.', 'mailchimp-for-wp' );
		echo ' ' . sprintf( __( 'Please <a href="%s">read through our upgrade guide</a> to make sure you can safely update.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/upgrading-to-3-0/' );
		echo '<br /><br />';
		echo sprintf( '<a class="button button-primary" href="%s">' . __( 'Update the plugin', 'mailchimp-for-wp' ) . '</a>', add_query_arg( array( self::OPTION_ENABLE_MAJOR_UPDATES => 1 ) ) );

		if( $pagenow === 'plugins.php' ) {
			echo ' &nbsp; <a href="'. add_query_arg( array( self::OPTION_DISMISS_NOTICE => 1 ) ) .'" class="button">Remind me next week</a>';
		}

		echo '</p>';
		echo '</div>';
	}
}