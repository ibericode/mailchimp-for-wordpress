<?php

/**
 * Block certain IP addresses from signing up through forms.
 */

add_filter('mc4wp_form_errors', function ($errors) {

    // add your blocked IP Addresses here.
    $blocked_ips = [
        '123.456.789.1'
    ];

    if (in_array($_SERVER['REMOTE_ADDR'], $blocked_ips)) {
        $errors[] = 'spam';
    }

    return $errors;
});
