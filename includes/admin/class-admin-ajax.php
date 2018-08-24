<?php

class PL4WP_Admin_Ajax {

    /**
     * @var PL4WP_Admin_Tools
     */
    protected $tools;

    /**
     * PL4WP_Admin_Ajax constructor.
     *
     * @param PL4WP_Admin_Tools $tools
     */
    public function __construct( PL4WP_Admin_Tools $tools )
    {
        $this->tools = $tools;
    }

    /**
     * Hook AJAX actions
     */
    public function add_hooks() {
        add_action( 'wp_ajax_pl4wp_renew_phplist_lists', array( $this, 'refresh_phplist_lists' ) );
    }

    /**
     * Empty lists cache & fetch lists again.
     */
	public function refresh_phplist_lists() {
        if( ! $this->tools->is_user_authorized() ) {
            wp_send_json(false);
        }

        $phplist = new PL4WP_PhpList();
        $success = $phplist->fetch_lists();
        wp_send_json( $success );
    }


}
