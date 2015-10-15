<?php
// prevent direct file access
defined( 'ABSPATH' ) or exit;
?>

<div class="mc4wp-box">
	<?php
	// upgrade block
	$block = new MC4WP_Remote_Content_Block( 'https://mc4wp.com/api/content-blocks?id=98121', include dirname( __FILE__ ) . '/upgrade-block-content.php' );
	echo $block;
	?>
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