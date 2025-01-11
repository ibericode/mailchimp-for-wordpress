<?php

/**
* Disable all object tracking, so that updating a product or order won't trigger a MailChimp sync.
* This overrides the setting on the MailChimp for WP > E-Commerce settings page.
*/

add_filter('mc4wp_ecommerce_options', function ($opts) {
    $opts['enable_object_tracking'] = false;
    return $opts;
});
