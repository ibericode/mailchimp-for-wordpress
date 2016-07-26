<?php defined( 'ABSPATH') or exit;

echo '<div class="notice is-dismissible updated">';
echo '<h4>' . __( 'Version 4.0 of MailChimp for WordPress is available for you', 'mailchimp-for-wp' ) . '</h4>';
echo '<p>' . __( 'This version updates the plugin to use the new MailChimp API (a server the plugin "talks" to).', 'mailchimp-for-wp' ) . '</p>';

echo '<p>';
echo __( 'However, MailChimp changed a few things on their end which forced us to do the same.', 'mailchimp-for-wp' );
echo ' ' . sprintf( __( 'Please <a href="%s">read through our upgrade guide carefully</a> so you are aware of the changes before updating the plugin.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/upgrading-to-4-0/ " style="font-weight: bold;' );
echo '<br /><br />';
echo sprintf( '<a class="button button-primary" href="%s">' . __( 'I am ready to update', 'mailchimp-for-wp' ) . '</a>', $update_link );
echo '</p>';
echo '</div>';