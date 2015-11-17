<?php defined( 'ABSPATH' ) or exit; ?>

<div class="medium-margin">

	<?php if( stripos( get_locale(), 'en_us' ) !== 0 ) { ?>
		<p class="help"><?php printf( __( 'MailChimp for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Head over to <a href="%s">the translation project and click "help translate"</a>.', 'mailchimp-for-wp' ), 'https://www.transifex.com/projects/p/mailchimp-for-wordpress/' ); ?></p>
	<?php } ?>

	<?php
	if( strpos( $_SERVER['HTTP_HOST'], 'local' ) === 0 ) { ?>
		<p class="help">Developer? Follow or contribute to the <a href="https://github.com/ibericode/mailchimp-for-wordpress">MailChimp for WordPress project on GitHub</a>.</p>
	<?php } ?>

	<?php do_action( 'mc4wp_admin_footer' ); ?>

	<p class="help"><?php _e( 'This plugin is not developed by or affiliated with MailChimp in any way.', 'mailchimp-for-wp' ); ?></p>
</div>