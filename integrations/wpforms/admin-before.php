<?php defined('ABSPATH') or exit; ?>

<p>
    <?php
    // translators: %s is the URL to the WPForms overview page.
    echo wp_kses(
        sprintf(
            __('Use this integration by adding the "Mailchimp" field to <a href="%s">your WPForms forms</a>.', 'mailchimp-for-wp'),
            esc_url(admin_url('admin.php?page=wpforms-overview'))
        ),
        [ 'a' => [ 'href' => [] ] ]
    );
    ?>
</p>
