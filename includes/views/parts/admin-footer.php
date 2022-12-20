<?php defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function _mc4wp_admin_translation_notice() {
	// show for every language other than the default
	if ( stripos( get_locale(), 'en_us' ) === 0 ) {
		return;
	}

	/* translators: %s links to the WordPress.org translation project */
	echo '<p class="description">' . sprintf( wp_kses( __( 'Mailchimp for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Please <a href="%s">help translate the plugin using your WordPress.org account</a>.', 'mailchimp-for-wp' ), array( 'a' => array( 'href' => array() ) ) ), 'https://translate.wordpress.org/projects/wp-plugins/mailchimp-for-wp/stable/' ) . '</p>';
}

/**
 * @ignore
 */
function _mc4wp_admin_github_notice() {
	if ( strpos( $_SERVER['HTTP_HOST'], 'localhost' ) === false && ! WP_DEBUG ) {
		return;
	}

	echo '<p class="description">Developer? Follow <a href="https://github.com/ibericode/mailchimp-for-wordpress">Mailchimp for WordPress on GitHub</a> or have a look at our repository of <a href="https://github.com/ibericode/mailchimp-for-wordpress/tree/master/sample-code-snippets">sample code snippets</a>.</p>';
}

/**
 * @ignore
 */
function _mc4wp_admin_disclaimer_notice() {
	echo '<p class="description">', esc_html__( 'This plugin is not developed by or affiliated with Mailchimp in any way.', 'mailchimp-for-wp' ), '</p>';
}

add_action( 'mc4wp_admin_footer', '_mc4wp_admin_translation_notice', 20 );
add_action( 'mc4wp_admin_footer', '_mc4wp_admin_github_notice', 50 );
add_action( 'mc4wp_admin_footer', '_mc4wp_admin_disclaimer_notice', 80 );
?>

<div class="mc4wp-margin-l">

	<?php

	/**
	 * Runs while printing the footer of every Mailchimp for WordPress settings page.
	 *
	 * @since 3.0
	 */
	do_action( 'mc4wp_admin_footer' );
	?>

</div>
