<?php

defined('ABSPATH') or exit;

$old_options = get_option('mc4wp_lite_checkbox', array());
$pro_options = get_option('mc4wp_checkbox', array());
if (! empty($pro_options)) {
    $old_options = array_merge($old_options, $pro_options);
}

// do we have to do something?
if (empty($old_options)) {
    return;
}

// find activated integrations (show_at_xxx options)
$new_options = array();
$map         = array(
    'comment_form'         => 'wp-comment-form',
    'registration_form'    => 'wp-registration-form',
    'buddypress_form'      => 'buddypress',
    'bbpres_forms'         => 'bbpress',
    'woocommerce_checkout' => 'woocommerce',
    'edd_checkout'         => 'easy-digital-downloads',
);

$option_keys = array(
    'label',
    'precheck',
    'css',
    'lists',
    'double_optin',
    'update_existing',
    'replace_interests',
    'send_welcome',
);

foreach ($map as $old_integration_slug => $new_integration_slug) {
    // check if integration is enabled using its old slug
    $show_key = sprintf('show_at_%s', $old_integration_slug);
    if (empty($old_options[ $show_key ])) {
        continue;
    }

    $options = array(
        'enabled' => 1,
    );

    foreach ($option_keys as $option_key) {
        if (isset($old_options[ $option_key ])) {
            $options[ $option_key ] = $old_options[ $option_key ];
        }
    }

    // add to new options
    $new_options[ $new_integration_slug ] = $options;
}

// save new settings
update_option('mc4wp_integrations', $new_options);

// delete old options
delete_option('mc4wp_lite_checkbox');
delete_option('mc4wp_checkbox');
