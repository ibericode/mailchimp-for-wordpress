<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

echo '<br style="clear: both;" />';

do_action( 'mc4wp_admin_footer' );

// show translation text when plugin is running in different language than source language
if( get_locale() !== 'en_US' ) {
	echo '<p class="help">' . sprintf( __( 'MailChimp for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Head over to <a href="%s">the translation project and click "help translate"</a>.', 'mailchimp-for-wp' ), 'https://www.transifex.com/projects/p/mailchimp-for-wordpress/' ) .'</p>';
}

// show notice to developers
if( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	echo '<p class="help">' . sprintf( __( 'Stay up to date of development of this plugin, <a href="%s">follow the MailChimp for WordPress project on GitHub</a>.', 'mailchimp-for-wp' ), 'https://github.com/ibericode/mailchimp-for-wordpress' ) . '</p>';
 }

// make it clear that we're not mailchimp
echo '<p class="help">' . __( 'This plugin is not developed by or affiliated with MailChimp in any way.', 'mailchimp-for-wp' ) . '</p>';