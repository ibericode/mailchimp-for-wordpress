<?php defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function __mc4wp_admin_translation_notice() {

	// show for every language other than the default
	if( stripos( get_locale(), 'en_us' ) === 0 ) {
		return;
	}

	// TODO: Check translation progress from Transifex here. Only show when < 100.

	echo '<p class="help">' . sprintf( __( 'MailChimp for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Head over to <a href="%s">the translation project and click "help translate"</a>.', 'mailchimp-for-wp' ), 'https://www.transifex.com/projects/p/mailchimp-for-wordpress/' ) . '</p>';
}

/**
 * @ignore
 */
function __mc4wp_admin_github_notice() {

	if( strpos( $_SERVER['HTTP_HOST'], 'local' ) !== 0 && ! WP_DEBUG ) {
		return;
	}

	echo '<p class="help">Developer? Check out our <a href="http://developer.mc4wp.com/">developer documentation</a> or follow <a href="https://github.com/ibericode/mailchimp-for-wordpress">MailChimp for WordPress on GitHub</a>.</p>';

}

/**
 * @ignore
 */
function __mc4wp_admin_disclaimer_notice() {
	echo '<p class="help">' . __( 'This plugin is not developed by or affiliated with MailChimp in any way.', 'mailchimp-for-wp' ) . '</p>';
}

add_action( 'mc4wp_admin_footer', '__mc4wp_admin_translation_notice' , 20);
add_action( 'mc4wp_admin_footer', '__mc4wp_admin_github_notice', 50 );
add_action( 'mc4wp_admin_footer', '__mc4wp_admin_disclaimer_notice', 80 );
?>

<div class="big-margin">

	<?php

	/**
	 * Runs while printing the footer of every MailChimp for WordPress settings page.
	 *
	 * @since 3.0
	 */
	do_action( 'mc4wp_admin_footer' ); ?>

</div>