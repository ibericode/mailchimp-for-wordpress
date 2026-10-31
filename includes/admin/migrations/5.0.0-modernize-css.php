<?php

/**
 * Migration: Modernize CSS themes.
 *
 * Converts forms using color themes (theme-*) to basic stylesheet.
 * Rebuilds the mc4wp_form_stylesheets option without 'themes'.
 *
 * @since 5.0.0
 */

defined('ABSPATH') or exit;

$posts = get_posts(
    [
        'post_type'   => 'mc4wp-form',
        'post_status' => 'any',
        'numberposts' => -1,
    ]
);

$stylesheets = [];

foreach ($posts as $post) {
    $options = get_post_meta($post->ID, '_mc4wp_settings', true);
    if (! is_array($options)) {
        $options = [];
    }

    // Convert color theme selections to basic.
    if (isset($options['css']) && strpos($options['css'], 'theme-') === 0) {
        $options['css'] = 'basic';
        update_post_meta($post->ID, '_mc4wp_settings', $options);
    }

    // Collect active stylesheets.
    if (! empty($options['css'])) {
        $stylesheet = $options['css'];

        if (! in_array($stylesheet, $stylesheets, true)) {
            $stylesheets[] = $stylesheet;
        }
    }
}

update_option('mc4wp_form_stylesheets', $stylesheets);
