<?php
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
?>
<div class="mc4wp-box">
	<h4 class="mc4wp-title"><?php _e( 'Looking for help?', 'mailchimp-for-wp' ); ?></h4>
	<p><?php printf( __( 'Make sure to look at the <a href="%s">MailChimp for WordPress documentation</a>, the plugin <a href="%s">FAQ</a> or use the <a href="%s">support forums</a> on WordPress.org.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/', 'https://wordpress.org/plugins/mailchimp-for-wp/faq/', 'https://wordpress.org/support/plugin/mailchimp-for-wp' ); ?></p>
</div>

<div class="mc4wp-box">
	<h4 class="mc4wp-title"><?php _e( 'Do you enjoy this plugin?', 'mailchimp-for-wp' ); ?></h4>
	<ul class="ul-square">
		<li><a target="_blank" href="https://mc4wp.com/"><?php printf( __( 'Upgrade to MailChimp for WordPress Pro', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&show-appreciation' ); ?></a></li>
		<li><a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/mailchimp-for-wp?rate=5#postform"><?php printf( __( 'Leave a %s plugin review on WordPress.org', 'mailchimp-for-wp' ), '&#9733;&#9733;&#9733;&#9733;&#9733;' ); ?></a></li>
		<li><a target="_blank" href="https://twitter.com/?status=<?php echo urlencode( __( 'I am using MailChimp for WordPress by @DannyvanKooten - it is great!', 'mailchimp-for-wp' ) . ' > https://mc4wp.com/' ); ?>"><?php _e( 'Tweet about MailChimp for WordPress', 'mailchimp-for-wp' ); ?></a></li>
		<li><?php echo sprintf( __( 'Review the plugin on your blog and link to <a href="%s">the plugin page</a>.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=show-appreciation' ) . ' ' . sprintf( __( 'There is an <a href="%s">affiliate program</a> as well.', 'mailchimp-for-wp' ), 'https://mc4wp.com/affiliates/' ); ?></li>
		<li><a target="_blank" href="https://wordpress.org/plugins/mailchimp-for-wp/"><?php _e( 'Vote "works" on the WordPress.org plugin page', 'mailchimp-for-wp' ); ?></a></li>
	</ul>
</div>