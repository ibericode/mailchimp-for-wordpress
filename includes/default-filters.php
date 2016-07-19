<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'mc4wp_form_data', 'mc4wp_add_name_data', 60 );
add_filter( 'mc4wp_integration_data', 'mc4wp_add_name_data', 60 );

add_filter( 'mctb_data', 'mc4wp_update_groupings_data', 90 );
add_filter( 'mc4wp_form_data', 'mc4wp_update_groupings_data', 90 );
add_filter( 'mc4wp_integration_data', 'mc4wp_update_groupings_data', 90 );

add_filter( 'mc4wp_use_sslverify', '__mc4wp_use_sslverify', 1 );
