<?php

mc4wp_register_integration('wpforms', 'MC4WP_WPForms_Integration', true);

function _mc4wp_wpforms_register_field()
{
    if (! class_exists('WPForms_Field')) {
        return;
    }

    new MC4WP_WPForms_Field();
}

add_action('init', '_mc4wp_wpforms_register_field');
