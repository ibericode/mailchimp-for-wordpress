<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'mc4wp_lists', 'mc4wp_wrap_in_array', 99 );
add_filter( 'mc4wp_form_lists', 'mc4wp_wrap_in_array', 99 );
add_filter( 'mc4wp_integration_lists', 'mc4wp_wrap_in_array', 99 );
add_filter( 'mc4wp_merge_vars', array( 'MC4WP_Tools', 'guess_merge_vars' ) );