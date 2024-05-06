<?php

defined('ABSPATH') or exit;

add_filter('mc4wp_form_data', 'mc4wp_add_name_data', 60);
add_filter('mc4wp_integration_data', 'mc4wp_add_name_data', 60);

add_filter('mctb_data', '_mc4wp_update_groupings_data', PHP_INT_MAX);
add_filter('mc4wp_form_data', '_mc4wp_update_groupings_data', PHP_INT_MAX);
add_filter('mc4wp_integration_data', '_mc4wp_update_groupings_data', PHP_INT_MAX);
add_filter('mailchimp_sync_user_data', '_mc4wp_update_groupings_data', PHP_INT_MAX);
add_filter('mc4wp_use_sslverify', '_mc4wp_use_sslverify', 1);

mc4wp_apply_deprecated_filters('mc4wp_merge_vars', 'mc4wp_form_data');
mc4wp_apply_deprecated_filters('mc4wp_form_merge_vars', 'mc4wp_form_data');
mc4wp_apply_deprecated_filters('mc4wp_integration_merge_vars', 'mc4wp_integration_data');
