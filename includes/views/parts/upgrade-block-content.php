<?php ob_start(); ?>

<div class="mc4wp-box" id="mc4wp-upgrade-box">
	<h3>MailChimp for WordPress Pro</h3>
	<p><em><?php _e( 'This plugin has an even better premium version, you will absolutely love it.', 'mailchimp-for-wp' ); ?></em></p>
	<p><?php _e( 'Some of the benefits over this free version:', 'mailchimp-for-wp' ); ?></p>
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
		<a class="button mc4wp-button" href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'Upgrade to Pro', 'mailchimp-for-wp' ); ?></a>
		<a class="" href="https://mc4wp.com/demo/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'View Demo', 'mailchimp-for-wp' ); ?></a>
	</p>
	<p style="text-align: center; margin-bottom: 0;">
		<small><?php _e( 'You can <strong>try with absolutely 0 risk</strong> using our refund policy.', 'mailchimp-for-wp' ); ?></small>
	</p>
</div>

<?php return ob_end_clean(); ?>