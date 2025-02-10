<?php

add_filter('mc4wp_integration_checkbox_label', function ($text, $integration) {
    return apply_filters('wpml_translate_single_string', $text, 'mailchimp-for-wp', "{$integration->name} checkbox label");
}, 10, 2);
