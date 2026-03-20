<?php

/**
 * Class MC4WP_Admin_Cron_Notice
 *
 * Shows a warning notice when WP-Cron is more than one hour behind schedule.
 *
 * @since 4.13.0
 * @ignore
 */
class MC4WP_Admin_Cron_Notice
{
    /**
     * @var MC4WP_Admin_Tools
     */
    protected $tools;

    /**
     * @var string
     */
    protected $meta_key_dismissed = '_mc4wp_cron_notice_dismissed';

    /**
     * MC4WP_Admin_Cron_Notice constructor.
     *
     * @since 4.13.0
     * @param MC4WP_Admin_Tools $tools
     */
    public function __construct(MC4WP_Admin_Tools $tools)
    {
        $this->tools = $tools;
    }

    /**
     * Add action & filter hooks.
     *
     * @since 4.13.0
     */
    public function add_hooks(): void
    {
        add_action('admin_notices', [ $this, 'show' ]);
        add_action('mc4wp_admin_dismiss_cron_notice', [ $this, 'dismiss' ]);
    }

    /**
     * Set flag in user meta so notice won't be shown again.
     *
     * @since 4.13.0
     */
    public function dismiss(): void
    {
        $user = wp_get_current_user();
        update_user_meta($user->ID, $this->meta_key_dismissed, 1);
    }

    /**
     * Show the cron warning notice if the scheduled event is behind.
     *
     * @since 4.13.0
     */
    public function show(): void
    {
        // only show on Mailchimp for WordPress' pages
        if (! $this->tools->on_plugin_page()) {
            return;
        }

        // only show to authorized users
        if (! $this->tools->is_user_authorized()) {
            return;
        }

        // only show if user did not dismiss before
        $user = wp_get_current_user();
        if (get_user_meta($user->ID, $this->meta_key_dismissed, true)) {
            return;
        }

        // check if cron is behind schedule
        if (! $this->is_cron_behind_schedule()) {
            return;
        }

        echo '<div class="notice notice-warning mc4wp-is-dismissible">';
        echo '<p>';
        echo esc_html__('Heads up! The scheduled Mailchimp for WordPress cron event appears to be running behind schedule. This could mean WP-Cron is not functioning correctly on your site. Please check your site\'s cron configuration.', 'mailchimp-for-wp');
        echo '</p>';
        echo '<form method="POST">';
        echo '<button type="submit" class="notice-dismiss"><span class="screen-reader-text">', esc_html__('Dismiss this notice.', 'mailchimp-for-wp'), '</span></button>';
        echo '<input type="hidden" name="_mc4wp_action" value="dismiss_cron_notice" />';
        wp_nonce_field('_mc4wp_action', '_wpnonce', true, true);
        echo '</form>';
        echo '</div>';
    }

    /**
     * Check whether the plugin's cron event is more than one hour behind schedule.
     *
     * @since 4.13.0
     * @return bool True if cron is behind schedule, false otherwise.
     */
    public function is_cron_behind_schedule(): bool
    {
        $next_scheduled = wp_next_scheduled('mc4wp_refresh_mailchimp_lists');

        // no event scheduled at all — don't show warning for this edge case
        // as the plugin may not have been activated via the normal flow
        if ($next_scheduled === false) {
            return false;
        }

        // if the next scheduled time is more than 1 hour in the past, cron is behind
        return $next_scheduled < (time() - HOUR_IN_SECONDS);
    }
}
