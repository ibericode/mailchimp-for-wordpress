<?php

/**
 * Class MC4WP_Admin_Texts
 *
 * @ignore
 * @since 3.0
 */
class MC4WP_Admin_Texts {

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
		global $pagenow;

		add_filter( 'admin_footer_text', array( $this, 'footer_text' ) );

		// Hooks for Plugins overview page
		if( $pagenow === 'plugins.php' ) {
			add_filter( 'plugin_action_links_' . $this->plugin_file, array( $this, 'add_plugin_settings_link' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links'), 10, 2 );
		}
	}

	/**
	 * Ask for a plugin review in the WP Admin footer, if this is one of the plugin pages.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public function footer_text( $text ) {

		if(! empty( $_GET['page'] ) && strpos( $_GET['page'], 'mailchimp-for-wp' ) === 0 ) {
			$text = sprintf( 'If you enjoy using <strong>MailChimp for WordPress</strong>, please <a href="%s" target="_blank">leave us a ★★★★★ rating</a>. A <strong style="text-decoration: underline;">huge</strong> thank you in advance!', 'https://wordpress.org/support/view/plugin-reviews/mailchimp-for-wp?rate=5#postform' );
		}

		return $text;
	}

	/**
	 * Add the settings link to the Plugins overview
	 *
	 * @param array $links
	 * @param       $file
	 *
	 * @return array
	 */
	public function add_plugin_settings_link( $links, $file ) {
		if( $file !== $this->plugin_file ) {
			return $links;
		}

		$settings_link = '<a href="' . admin_url( 'admin.php?page=mailchimp-for-wp' ) . '">'. __( 'Settings', 'mailchimp-for-wp' ) . '</a>';
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

		$links[] = '<a href="https://mc4wp.com/kb/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-page">'. __( 'Documentation', 'mailchimp-for-wp' ) . '</a>';

		/**
		 * Filters meta links shown on the Plugins overview page
		 *
		 * This takes an array of strings
		 *
		 * @since 3.0
		 * @param array $links
		 */
		$links = (array) apply_filters( 'mc4wp_admin_plugin_meta_links', $links );

		return $links;
	}
}