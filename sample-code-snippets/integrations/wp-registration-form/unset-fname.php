<?php

/**
 * Unset the "FNAME" field that is sent via WP Registration integration.
 *
 */

add_filter('mc4wp_integration_wp-registration-form_data', function ($data) {
    unset($data['FNAME']);
    return $data;
});
