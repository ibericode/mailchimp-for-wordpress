<?php

defined( 'ABSPATH' ) or exit;

if( function_exists( 'pl4wp_refresh_phplist_lists' ) ) {
	pl4wp_refresh_phplist_lists();
}

delete_transient( 'pl4wp_phplist_lists_v3' );
delete_option( 'pl4wp_phplist_lists_v3_fallback' );

wp_schedule_event( strtotime('tomorrow 3 am'), 'daily', 'pl4wp_refresh_phplist_lists' );

