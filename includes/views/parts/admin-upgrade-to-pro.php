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
		<a class="button button-primary button-large" href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'Upgrade Now', 'mailchimp-for-wp' ); ?></a>
		<a class="button" href="https://mc4wp.com/demo/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'View Demo', 'mailchimp-for-wp' ); ?></a>
	</p>
</div>
<div class="mc4wp-box" id="mc4wp-optin-box">

	<?php $user = wp_get_current_user(); ?>
	<!-- Begin MailChimp Signup Form -->
	<div id="mc_embed_signup">
		<h4 class="mc4wp-title"><?php _e( 'More subscribers, better newsletters.', 'mailchimp-for-wp' ); ?></h4>
		<p><?php _e( 'Learn how to best grow your lists & write better emails by subscribing to our monthly tips.', 'mailchimp-for-wp' ); ?></p>
		<form action="//mc4wp.us1.list-manage.com/subscribe/post?u=a2d08947dcd3683512ce174c5&amp;id=a940232df9" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" target="_blank">
			<p>
				<label>Email Address </label>
				<input type="email" value="<?php echo esc_attr( $user->user_email ); ?>" name="EMAIL" class="regular-text" required>
			</p>
			<p>
				<label>First Name </label>
				<input type="text" value="<?php echo esc_attr( $user->user_firstname ); ?>" name="FNAME" class="regular-text" id="mce-FNAME">
			</p>
			<div style="position: absolute; left: -5000px;">
				<input type="text" name="b_a2d08947dcd3683512ce174c5_a940232df9" tabindex="-1" value="" />
			</div>
			<p>
				<input type="submit" value="Subscribe" name="subscribe" class="button">
			</p>

			<input type="hidden" name="SOURCE" value="free-plugin" />
		</form>
	</div>
</div>