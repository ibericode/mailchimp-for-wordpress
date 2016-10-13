<?php

class MC4WP_Admin_Tools {

    public function __construct() {
    }

    /**
     * @param string $page
     *
     * @return bool
     */
    public function on_plugin_page( $page = '') {
        $page = $page ? 'mailchimp-for-wp-'. $page : 'mailchimp-for-wp';
        return isset( $_GET['page'] ) && strpos( $_GET['page'], $page ) === 0;
    }

    /**
     * Does the logged-in user have the required capability?
     *
     * @return bool
     */
    public function is_user_authorized() {
        return current_user_can( $this->get_required_capability() );
    }

    /**
     * Get required capability to access settings page and view dashboard widgets.
     *
     * @return string
     */
    public function get_required_capability() {

        $capability = 'manage_options';

        /**
         * Filters the required user capability to access the settings pages & dashboard widgets.
         *
         * @ignore
         * @deprecated 3.0
         */
        $capability = apply_filters( 'mc4wp_settings_cap', $capability );

        /**
         * Filters the required user capability to access the MailChimp for WordPress' settings pages, view the dashboard widgets.
         *
         * Defaults to `manage_options`
         *
         * @since 3.0
         * @param string $capability
         * @see https://codex.wordpress.org/Roles_and_Capabilities
         */
        $capability = (string) apply_filters( 'mc4wp_admin_required_capability', $capability );

        return $capability;
    }

}