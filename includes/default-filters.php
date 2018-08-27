<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'pl4wp_form_data', 'pl4wp_add_name_data', 60 );
add_filter( 'pl4wp_integration_data', 'pl4wp_add_name_data', 60 );

add_filter( 'mctb_data', '_pl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'pl4wp_form_data', '_pl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'pl4wp_integration_data', '_pl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'phplist_sync_user_data', '_pl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'pl4wp_use_sslverify', '_pl4wp_use_sslverify', 1 );
