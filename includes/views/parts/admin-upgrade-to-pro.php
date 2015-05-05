<?php
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>
<div class="mc4wp-box" id="mc4wp-upgrade-box">
	<h3>MailChimp for WordPress Pro</h3>
	<p><em><?php _e( 'This plugin has an even better premium version, you will absolutely love it.', 'mailchimp-for-wp' ); ?></em></p>
	<p><?php _e( 'Some differences with this free version of the plugin:', 'mailchimp-for-wp' ); ?></p>
	<ul class="ul-square">
		<li>
			<strong><?php _e( 'Multiple forms', 'mailchimp-for-wp' ); ?></strong><br />
			<?php _e( 'Each subscribing to one or multiple MailChimp lists.', 'mailchimp-for-wp' ); ?>
		</li>
		<li>
			<strong><?php _e( 'AJAX forms', 'mailchimp-for-wp' ); ?></strong><br />
			<?php _e( 'Forms do not require a full page reload.', 'mailchimp-for-wp' ); ?>
		</li>
		<li>
			<strong><?php _e( 'Statistics', 'mailchimp-for-wp' ); ?></strong><br />
			<?php _e( 'Every form interaction is logged and visualised in insightful charts.', 'mailchimp-for-wp' ); ?>
		</li>
		<li>
			<strong><?php _e( 'Styles Builder', 'mailchimp-for-wp' ); ?></strong><br />
			<?php _e( 'Create beautiful form themes with ease.', 'mailchimp-for-wp' ); ?>
		</li>
	</ul>
	<p>
		<a class="button button-primary button-large" href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'Upgrade Now', 'mailchimp-for-wp' ); ?></a>
		<a class="button" href="https://mc4wp.com/demo/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'View Demo', 'mailchimp-for-wp' ); ?></a></p>
	</p>
</div>