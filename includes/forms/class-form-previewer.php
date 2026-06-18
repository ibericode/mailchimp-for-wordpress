<?php

defined('ABSPATH') || exit;


class MC4WP_Form_Previewer
{
    public function add_hooks()
    {
        add_action('parse_request', [ $this, 'listen' ]);
    }

    public function listen()
    {
        if (empty($_GET['mc4wp_preview_form'])) {
            return;
        }

        if (! current_user_can('edit_posts')) {
            return;
        }

        show_admin_bar(false);
        add_filter('pre_handle_404', '__return_true');
        remove_all_actions('template_redirect');
        add_action('template_redirect', [ $this, 'load_preview' ]);
    }

    public function load_preview()
    {
        @ini_set('display_errors', 0); // phpcs:ignore

        // clear output, some plugin or hooked code might have thrown errors by now.
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $form_id = (int) ($_GET['mc4wp_preview_form'] ?? 0);
        status_header(200);

        require __DIR__ . '/views/preview.php';
        exit;
    }
}
