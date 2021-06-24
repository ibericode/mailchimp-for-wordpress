<?php

/**
 * Class MC4WP_Admin_Review_Notice
 *
 * @ignore
 */
class MC4WP_Admin_Review_Notice {


	/**
	 * @var MC4WP_Admin_Tools
	 */
	protected $tools;

	/**
	 * @var string
	 */
	protected $meta_key_dismissed = '_mc4wp_review_notice_dismissed';

	/**
	 * MC4WP_Admin_Review_Notice constructor.
	 *
	 * @param MC4WP_Admin_Tools $tools
	 */
	public function __construct( MC4WP_Admin_Tools $tools ) {
		$this->tools = $tools;
	}

	/**
	 * Add action & filter hooks.
	 */
	public function add_hooks() {
		add_action( 'admin_notices', array( $this, 'show' ) );
		add_action( 'mc4wp_admin_dismiss_review_notice', array( $this, 'dismiss' ) );
	}

	/**
	 * Set flag in user meta so notice won't be shown.
	 */
	public function dismiss() {
		$user = wp_get_current_user();
		update_user_meta( $user->ID, $this->meta_key_dismissed, 1 );
	}

	/**
	 * @return bool
	 */
	public function show() {
		// only show on Mailchimp for WordPress' pages.
		if ( ! $this->tools->on_plugin_page() ) {
			return false;
		}

		// only show if 2 weeks have passed since first use.
		$two_weeks_in_seconds = ( 60 * 60 * 24 * 14 );
		if ( $this->time_since_first_use() <= $two_weeks_in_seconds ) {
			return false;
		}

		// only show if user did not dismiss before
		$user = wp_get_current_user();
		if ( get_user_meta( $user->ID, $this->meta_key_dismissed, true ) ) {
			return false;
		}

		echo '<div class="notice notice-info mc4wp-is-dismissible" id="mc4wp-review-notice">';
		echo '<p>';
		echo esc_html__( 'You\'ve been using Mailchimp for WordPress for some time now; we hope you love it!', 'mailchimp-for-wp' ), ' <br />';
		echo sprintf( wp_kses( __( 'If you do, please <a href="%s">leave us a 5★ rating on WordPress.org</a>. It would be of great help to us.', 'mailchimp-for-wp' ), array( 'a' => array( 'href' => array() ) ) ), 'https://wordpress.org/support/view/plugin-reviews/mailchimp-for-wp?rate=5#new-post' );
		echo '</p>';
		echo '<form method="POST" id="mc4wp-dismiss-review-form"><button type="submit" class="notice-dismiss"><span class="screen-reader-text">', esc_html__( 'Dismiss this notice.', 'mailchimp-for-wp' ), '</span></button><input type="hidden" name="_mc4wp_action" value="dismiss_review_notice" />', wp_nonce_field( '_mc4wp_action', '_wpnonce', true, false ), '</form>';
		echo '</div>';
		return true;
	}

	/**
	 * @return int
	 */
	private function time_since_first_use() {
		$options = get_option( 'mc4wp' );

		// option was never added before, do it now.
		if ( empty( $options['first_activated_on'] ) ) {
			$options['first_activated_on'] = time();
			update_option( 'mc4wp', $options );
		}

		return time() - $options['first_activated_on'];
	}
}
