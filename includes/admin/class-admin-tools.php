<?php

class MC4WP_Admin_Tools
{
    /**
     * @return string
     */
    public function get_plugin_page()
    {
        if (empty($_GET['page'])) {
            return '';
        }

        return ltrim(substr(wp_unslash($_GET['page']), strlen('mailchimp-for-wp')), '-');
    }

    /**
     * @param string $page
     *
     * @return bool
     */
    public function on_plugin_page($page = null)
    {
        // any settings page
        if ($page === null) {
            return isset($_GET['page']) && strpos(wp_unslash($_GET['page']), 'mailchimp-for-wp') === 0;
        }

        // specific page
        return $this->get_plugin_page() === $page;
    }

    /**
     * Does the logged-in user have the required capability?
     *
     * @return bool
     */
    public function is_user_authorized()
    {
        return current_user_can($this->get_required_capability());
    }

    /**
     * Get required capability to access settings page and view dashboard widgets.
     *
     * @return string
     */
    public function get_required_capability()
    {
        $capability = 'manage_options';

        /**
         * Filters the required user capability to access the Mailchimp for WordPress' settings pages, view the dashboard widgets.
         *
         * Defaults to `manage_options`
         *
         * @since 3.0
         * @param string $capability
         * @see https://codex.wordpress.org/Roles_and_Capabilities
         */
        $capability = (string) apply_filters('mc4wp_admin_required_capability', $capability);

        return $capability;
    }
}
