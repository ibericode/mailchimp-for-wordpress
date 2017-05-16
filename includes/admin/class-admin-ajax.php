<?php

class MC4WP_Admin_Ajax {

    /**
     * @var MC4WP_Admin_Tools
     */
    protected $tools;

    /**
     * MC4WP_Admin_Ajax constructor.
     *
     * @param MC4WP_Admin_Tools $tools
     */
    public function __construct( MC4WP_Admin_Tools $tools )
    {
        $this->tools = $tools;
    }

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
        if( ! $this->tools->is_user_authorized() ) {
            wp_send_json(false);
        }

        $mailchimp = new MC4WP_MailChimp();
        $success = $mailchimp->fetch_lists();
        wp_send_json( $success );
    }


}