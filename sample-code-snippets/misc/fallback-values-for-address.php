<?php

/**
 * Mailchimp always expects a full and complete address when you use a field of type "address"
 *
 * This snippet will allow you to submit a partial address and fill missing fields with fallback values.
 * Fill sensible fallback data for your region, especially ZIP might expect a different value depending on your locale.
 */

add_filter('mc4wp_format_field_value', function ($value, $field_type) {
    if ($field_type === 'address') {
        $value = is_array($value) ? $value : [];

        $fallbacks = [
            'addr1'   => 'N/A',
            'city'    => 'N/A',
            'state'   => 'N/A',
            'zip'     => '00000',
        ];

        // Fill missing values from fallbacks
        foreach ($fallbacks as $k => $fallback) {
            $value[$k] = isset($value[$k]) && $value[$k] !== '' ? $value[$k] : $fallback;
        }
    }
    return $value;
}, 10, 2);
