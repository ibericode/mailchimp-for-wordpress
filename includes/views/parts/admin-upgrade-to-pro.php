<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>
<div class="mc4wp-box" id="mc4wp-upgrade-box">
	<h3>MailChimp for WordPress Pro</h3>
	
	<p><em><?php _e( 'This plugin has an even better premium version, I am sure you will love it.', 'mailchimp-for-wp' ); ?></em></p>
	<p><?php _e( 'Pro features include better and multiple forms, advanced and easy form styling, more default themes, detailed statistics and priority support.', 'mailchimp-for-wp' ); ?></p>
	<p><a href="https://mc4wp.com/#utm_source=lite-plugin&utm_medium=link&utm_campaign=upgrade-box"><?php _e( 'More information about MailChimp for WP Pro', 'mailchimp-for-wp' ); ?> &raquo;</a></p>
</div>