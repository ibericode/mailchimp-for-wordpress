<?php

defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function __mc4wp_admin_sidebar_support_notice() {
	?>
	<div class="mc4wp-box">
		<h4 class="mc4wp-title"><?php echo esc_html__( 'Looking for help?', 'mailchimp-for-wp' ); ?></h4>
		<p><?php echo __( 'We have some resources available to help you in the right direction.', 'mailchimp-for-wp' ); ?></p>
		<ul class="ul-square">
			<li><a href="https://mc4wp.com/kb/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar"><?php echo esc_html__( 'Knowledge Base', 'mailchimp-for-wp' ); ?></a></li>
			<li><a href="https://wordpress.org/plugins/mailchimp-for-wp/faq/"><?php echo esc_html__( 'Frequently Asked Questions', 'mailchimp-for-wp' ); ?></a></li>
			<li><a href="http://developer.mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar"><?php echo esc_html__( 'Code reference for developers', 'mailchimp-for-wp' ); ?></a></li>
		</ul>
		<p><?php echo sprintf( __( 'If your answer can not be found in the resources listed above, please use the <a href="%s">support forums on WordPress.org</a>.' ), 'https://wordpress.org/support/plugin/mailchimp-for-wp' ); ?></p>
		<p><?php echo sprintf( __( 'Found a bug? Please <a href="%s">open an issue on GitHub</a>.' ), 'https://github.com/ibericode/mailchimp-for-wordpress/issues' ); ?></p>
	</div>
	<?php
}

/**
 * @ignore
 */
function __mc4wp_admin_sidebar_boxzilla_notice() {

	// Don't show if Boxzilla is already running
	if( defined( 'BOXZILLA_VERSION' ) ) {
		return;
	}

	?>
	<div class="mc4wp-box">
		<h4 class="mc4wp-title"><?php echo esc_html__( 'Looking to improve your sign-up rates?', 'mailchimp-for-wp' ); ?></h4>
		<p><?php printf( __( 'Our <a href="%s">Boxzilla plugin</a> allows you to create pop-ups or slide-ins with a subscribe form. A sure way to grow your lists faster.', 'mailchimp-for-wp' ), 'https://boxzillaplugin.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar' ); ?></p>
	</div>
	<?php
}

add_action( 'mc4wp_admin_sidebar', '__mc4wp_admin_sidebar_boxzilla_notice', 40 );
add_action( 'mc4wp_admin_sidebar', '__mc4wp_admin_sidebar_support_notice', 50 );

/**
 * Runs when the sidebar is outputted on MailChimp for WordPress settings pages.
 *
 * Please note that not all pages have a sidebar.
 *
 * @since 3.0
 */
do_action( 'mc4wp_admin_sidebar' );
