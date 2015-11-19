<?php

/**
 * Class MC4WP_Ads
 *
 * @ignore
 * @access private
 */
class MC4WP_Ads {

	/**
	 * @return bool Adds hooks
	 */
	public function add_hooks() {

		// don't hook if Pro is activated
		if( defined( 'MC4WP_PRO_VERSION' ) ) {
			return false;
		}

		add_filter( 'mc4wp_admin_plugin_meta_links', array( $this, 'plugin_meta_links' ) );
		add_action( 'mc4wp_admin_sidebar', array( $this, 'admin_sidebar' ) );
		add_action( 'mc4wp_admin_footer', array( $this, 'admin_footer' ), 10 );
		return true;
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_meta_links( $links ) {
		$links[] = '<a href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-upgrade-link">' . __( 'Upgrade to MailChimp for WordPress Pro', 'mailchimp-for-wp' ) . '</a>';
		return $links;
	}

	/**
	 * Add upgrade text to admin footer.
	 */
	public function admin_footer() {
		echo '<p class="help">' . sprintf( __( 'Enjoying this plugin? <a href="%s">Purchase our bundle of premium features</a> for an even better plugin.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ) . '</p>';
	}

	/**
	 * Add upgrade block to sidebar
	 */
	public function admin_sidebar() {
		include MC4WP_PLUGIN_DIR . 'includes/views/parts/admin-upgrade-to-pro.php';
	}

}