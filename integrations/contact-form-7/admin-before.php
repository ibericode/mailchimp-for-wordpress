<?php defined('ABSPATH') or exit; ?>

<p>
    <?php
    echo wp_kses(
        sprintf(
            // translators: %s is the shortcode input element [mc4wp_checkbox].
            __('To integrate with Contact Form 7, configure the settings below and then add %s to your CF7 form mark-up.', 'mailchimp-for-wp'),
            '<input type="text" onfocus="this.select()" readonly value="' . esc_attr('[mc4wp_checkbox]') . '">'
        ),
        [
            'input' => [
                'type' => [],
                'onfocus' => [],
                'readonly' => [],
                'value' => [],
            ],
        ]
    );
    ?>
</p>
