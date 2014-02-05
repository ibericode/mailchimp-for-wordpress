<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
   
delete_option('mc4wp_lite');
delete_option('mc4wp_lite_checkbox');
delete_option('mc4wp_lite_form');

delete_transient('mc4wp_mailchimp_lists');
delete_transient('mc4wp_mailchimp_lists_fallback');
