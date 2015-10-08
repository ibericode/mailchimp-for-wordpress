<?php

/**
 * Class MC4WP_Ads
 *
 * @todo Write this class so it shows all "upgrade to pro" nags when Pro is not running.
 */
class MC4WP_Ads {

	public function add_hooks() {

		// don't hook if Pro is activated
		if( defined( 'MC4WP_PRO_VERSION' ) ) {
			return false;
		}

		add_filter( 'mc4wp_menu_items', array( $this, 'menu_items' ) );
		add_action( 'mc4wp_admin_before_sidebar', array( $this, 'admin_before_sidebar' ) );
		add_action( 'mc4wp_admin_footer', array( $this, 'admin_footer' ) );
		return true;
	}

	/**
	 * Add upgrade text to admin footer.
	 */
	public function admin_footer() {
		echo '<p class="help">' . sprintf( __( 'Enjoying this plugin? <a href="%s">Upgrade to MailChimp for WordPress Pro</a> for an even better plugin, you will love it.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ) . '</p>';
	}

	/**
	 * Add upgrade block to sidebar
	 */
	public function admin_before_sidebar() {
		include MC4WP_PLUGIN_DIR . 'includes/views/parts/admin-upgrade-to-pro.php';
	}

	/**
	 * Redirects to the premium version of MailChimp for WordPress (uses JS)
	 */
	public function redirect_to_pro() {
		$url = 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=menu-upgrade-link';

		if( ! headers_sent() ) {
			wp_redirect( "Location: $url;", 302 );
			exit;
		} else {
			echo '<p>' . sprintf( __( 'You will be redirected to <strong>%s</strong> in a few seconds. <a href="%s">Click here if you are not automatically redirected.</a>', 'mailchimp-for-wp' ), 'mc4wp.com', $url ) . '</p>';
			echo sprintf( '<script type="text/javascript">window.location.replace(\'%s\'); </script>', $url );
		}
	}

	/**
	 * @param array $items
	 *
	 * @return array
	 */
	public function menu_items( array $items ) {
		$items[] = array(
			'title' => __( 'Upgrade to Pro', 'mailchimp-for-wp' ),
			'text' => '<span style="line-height: 20px;"><span class="dashicons dashicons-external"></span> ' .__( 'Upgrade to Pro', 'mailchimp-for-wp' ),
			'slug' => 'upgrade',

			'callback' => array( $this, 'redirect_to_pro' ),
		);

		return $items;
	}

}