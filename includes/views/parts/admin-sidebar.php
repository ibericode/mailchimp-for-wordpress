<?php

defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function __mc4wp_admin_sidebar_support_notice() {
	?>
	<div class="mc4wp-box">
		<h4 class="mc4wp-title">Looking for help?</h4>
		<p>We have some resources available to help you in the right direction.</p>
		<ul class="ul-square">
			<li><a href="https://mc4wp.com/kb/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar">Knowledge Base</a></li>
			<li><a href="https://wordpress.org/plugins/mailchimp-for-wp/faq/">Frequently Asked Questions</a></li>
			<li><a href="http://developer.mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar">Code reference for developers</a></li>
		</ul>
		<p>If your answer can not be found in the resources listed above, please use the <a href="https://wordpress.org/support/plugin/mailchimp-for-wp">support forums on WordPress.org</a>.</p>
		<p>If you think you found an issue, please <a href="https://github.com/ibericode/mailchimp-for-wordpress/issues">open an issue on GitHub</a>.</p>
	</div>
	<?php
}

add_action( 'mc4wp_admin_sidebar', '__mc4wp_admin_sidebar_support_notice', 50 );

/**
 * Runs when the sidebar is outputted on MailChimp for WordPress settings pages.
 *
 * Please note that not all pages have a sidebar.
 *
 * @since 3.0
 */
do_action( 'mc4wp_admin_sidebar' );
