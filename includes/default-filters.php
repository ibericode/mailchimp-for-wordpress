<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'mc4wp_form_merge_vars', 'mc4wp_guess_merge_vars' );
add_filter( 'mc4wp_integration_merge_vars', 'mc4wp_guess_merge_vars' );

add_filter( 'mc4wp_use_sslverify', '__mc4wp_use_sslverify' );