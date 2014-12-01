<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>
<br style="clear:both;" />
<p class="help"><?php printf( __( 'MailChimp for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Head over to <a href="%s">the translation project and click "help translate"</a>.', 'mailchimp-for-wp' ), 'https://www.transifex.com/projects/p/mailchimp-for-wordpress/' ); ?></p>
<p class="help"><?php printf( __( 'Enjoying this plugin? <a href="%s">Upgrade to MailChimp for WordPress Pro now</a> for an even better plugin, you will love it.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=lite-plugin&utm_medium=link&utm_campaign=footer-link' ); ?></p>
<p class="help"><?php _e( 'This plugin is not developed by or affiliated with MailChimp in any way.', 'mailchimp-for-wp' ); ?></p>