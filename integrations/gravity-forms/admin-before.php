<?php defined('ABSPATH') or exit; ?>

<p>
    <?php
    echo wp_kses(
        sprintf(
            /* translators: %s links to the Gravity Forms overview page */
            __('To integrate with Gravity Forms, add the "Mailchimp for WordPress" field to <a href="%s">one of your Gravity Forms forms</a>.', 'mailchimp-for-wp'),
            esc_url(admin_url('admin.php?page=gf_edit_forms'))
        ),
        [ 'a' => [ 'href' => [] ] ]
    );
    ?>
</p>
