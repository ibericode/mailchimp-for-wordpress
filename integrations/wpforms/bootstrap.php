<?php

mc4wp_register_integration('wpforms', 'MC4WP_WPForms_Integration', true);

add_action('plugins_loaded', function () {
    if (class_exists('WPForms_Field')) {
        new MC4WP_WPForms_Field();
    }
});
