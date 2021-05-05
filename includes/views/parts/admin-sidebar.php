<?php

defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function _mc4wp_admin_sidebar_support_notice() {
	?>
	<div class="mc4wp-box mc4wp-margin-m">
		<h4 class="mc4wp-title"><?php echo esc_html__( 'Looking for help?', 'mailchimp-for-wp' ); ?></h4>
		<p><?php echo esc_html__( 'We have some resources available to help you in the right direction.', 'mailchimp-for-wp' ); ?></p>
		<ul class="ul-square">
			<li><a href="https://www.mc4wp.com/kb/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar"><?php echo esc_html__( 'Knowledge Base', 'mailchimp-for-wp' ); ?></a></li>
			<li><a href="https://wordpress.org/plugins/mailchimp-for-wp/faq/"><?php echo esc_html__( 'Frequently Asked Questions', 'mailchimp-for-wp' ); ?></a></li>
		</ul>
		<p><?php echo sprintf( wp_kses( __( 'If your answer can not be found in the resources listed above, please use the <a href="%s">support forums on WordPress.org</a>.', 'mailchimp-for-wp' ), array( 'a' => array( 'href' => array() ) ) ), 'https://wordpress.org/support/plugin/mailchimp-for-wp' ); ?></p>
		<p><?php echo sprintf( wp_kses( __( 'Found a bug? Please <a href="%s">open an issue on GitHub</a>.', 'mailchimp-for-wp' ), array( 'a' => array( 'href' => array() ) ) ), 'https://github.com/ibericode/mailchimp-for-wordpress/issues' ); ?></p>
	</div>
	<?php
}

/**
 * @ignore
 */
function _mc4wp_admin_sidebar_other_plugins() {
	echo '<div class="mc4wp-box mc4wp-margin-m">';
	echo '<h4 class="mc4wp-title">', esc_html__( 'Other plugins by ibericode', 'mailchimp-for-wp' ), '</h4>';
	echo '<ul style="margin-bottom: 0;">';

	// Koko Analytics
	echo '<li style="margin: 12px 0;">';
	echo sprintf( '<strong><a href="%s">Koko Analytics</a></strong><br />', 'https://wordpress.org/plugins/koko-analytics/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar' );
	echo esc_html__( 'Privacy-friendly analytics plugin that does not use any external services.', 'mailchimp-for-wp' );
	echo '</li>';

	// Boxzilla
	echo '<li style="margin: 12px 0;">';
	echo sprintf( '<strong><a href="%s">Boxzilla Pop-ups</a></strong><br />', 'https://wordpress.org/plugins/boxzilla/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar' );
	echo esc_html__( 'Pop-ups or boxes that slide-in with a newsletter sign-up form. A sure-fire way to grow your email lists.', 'mailchimp-for-wp' );
	echo '</li>';

	// HTML Forms
	echo '<li style="margin: 12px 0;">';
	echo sprintf( '<strong><a href="%s">HTML Forms</a></strong><br />', 'https://wordpress.org/plugins/html-forms/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar' );
	echo esc_html__( 'Super flexible forms using native HTML. Just like Mailchimp for WordPress forms but for other purposes, like a contact form.', 'mailchimp-for-wp' );
	echo '</li>';

	echo '</ul>';
	echo '</div>';
}

add_action( 'mc4wp_admin_sidebar', '_mc4wp_admin_sidebar_other_plugins', 40 );
add_action( 'mc4wp_admin_sidebar', '_mc4wp_admin_sidebar_support_notice', 50 );

/**
 * Runs when the sidebar is outputted on Mailchimp for WordPress settings pages.
 *
 * Please note that not all pages have a sidebar.
 *
 * @since 3.0
 */
do_action( 'mc4wp_admin_sidebar' );
