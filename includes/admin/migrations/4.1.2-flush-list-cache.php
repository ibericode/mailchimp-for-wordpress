<?php 

defined( 'ABSPATH' ) or exit;

if( function_exists( 'mc4wp_refresh_mailchimp_lists' ) ) {
	mc4wp_refresh_mailchimp_lists();
}

delete_transient( 'mc4wp_mailchimp_lists_v3' );
delete_option( 'mc4wp_mailchimp_lists_v3_fallback' );

wp_schedule_event( strtotime('tomorrow 3 am'), 'daily', 'mc4wp_refresh_mailchimp_lists' );

