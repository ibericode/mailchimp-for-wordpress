<?php

class MC4WP_Admin_Ajax {

    /**
     * Hook AJAX actions
     */
    public function add_hooks() {
        add_action( 'wp_ajax_mc4wp_renew_mailchimp_lists', array( $this, 'refresh_mailchimp_lists' ) );
    }

    /**
     * Empty lists cache & fetch lists again.
     */
    public function refresh_mailchimp_lists() {
        mc4wp_refresh_mailchimp_lists();
        wp_send_json(true);
    }


}