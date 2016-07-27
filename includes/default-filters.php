<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'mc4wp_form_data', 'mc4wp_add_name_data', 60 );
add_filter( 'mc4wp_integration_data', 'mc4wp_add_name_data', 60 );

add_filter( 'mctb_data', '__mc4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'mc4wp_form_data', '__mc4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'mc4wp_integration_data', '__mc4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'mailchimp_sync_user_data', '__mc4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'mc4wp_use_sslverify', '__mc4wp_use_sslverify', 1 );
