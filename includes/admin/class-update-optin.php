<?php

class MC4WP_Update_Optin {

    /**
     * @const string
     */
    const CAPABILITY = 'install_plugins';

    /**
     * @var string
     */
    protected $plugin_file = '';

    /**
     * @var string
     */
    protected $to_version;

    /**
     * @var string
     */
    protected $view_file;

    /**
     * @var string
     */
    protected $option_enable;

    /**
     * @var string
     */
    protected $option_notice;

    /**
     * @param string $to_version
     * @param string $plugin_file
     * @param string $view_file
     */
    public function __construct( $to_version, $plugin_file, $view_file ) {
        $this->to_version = $to_version;
        $this->plugin_file = $plugin_file;
        $this->view_file = $view_file;

        $this->option_enable = 'mc4wp_enable_' . sanitize_key( $this->to_version );
        $this->option_notice = 'mc4wp_notice_' . sanitize_key( $this->to_version );
    }

    /**
     * Add hooks
     */
    public function add_hooks() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'maybe_hide_update' ) );
        add_action( 'init', array( $this, 'listen' ) );

        global $pagenow;
        if( $pagenow === 'plugins.php' || $pagenow === 'update-core.php'  ) {
            add_action( 'admin_notices', array( $this, 'show_update_optin' ) );
        }
    }
    /**
     * Listen for actions
     */
    public function listen() {

        // only show to users with required capability
        if( ! current_user_can( self::CAPABILITY ) ) {
            return;
        }

        if( isset( $_GET[ $this->option_enable ] ) ) {
            $this->enable_major_updates();
        }
    }
    /**
     * Prevents updates from showing
     *
     * @param array $data
     * @return array
     */
    public function maybe_hide_update( $data ) {

        // for testing
//        delete_option( $this->option_enable );
//        delete_option( $this->option_notice );
//        $data->response[ $this->plugin_file ] = $data->no_update[ $this->plugin_file ];
//        $data->response[ $this->plugin_file ]->new_version = '4.0.0';

        // do nothing if there's no update to act upon
        if( empty( $data->response[ $this->plugin_file ]->new_version ) ) {
            return $data;
        }

        $wordpress_org_data = $data->response[ $this->plugin_file ];

        // reset notice flag here in case we revert the update
        if( version_compare( $wordpress_org_data->new_version, $this->to_version, '<' ) ) {
            update_option( $this->option_notice, 0 );
            return $data;
        }

        // return unmodified data if already opted-in
        $opted_in = get_option( $this->option_enable, false );
        if( $opted_in ) {
            return $data;
        }

        // set a flag to start showing "update to x.x" notice
        update_option( $this->option_notice, 1 );

        // unset update data
        unset( $data->response[ $this->plugin_file ] );

        return $data;
    }

    /**
     * Enables major updates (opts-in to 3.x update)
     */
    public function enable_major_updates() {
        // update option
        update_option( $this->option_enable, 1 );

        // delete site transient so wp core will fetch latest version
        delete_site_transient( 'update_plugins' );

        // redirect to updates page
        wp_safe_redirect( admin_url( 'update-core.php' ) );
        exit;
    }

    /**
     * Shows update opt-in
     */
    public function show_update_optin() {

        if( ! $this->should_show_update_optin() ) {
            return;
        }

        // prepare link URL
        $update_link = add_query_arg( array( $this->option_enable => 1 ) );

        // show!
       include $this->view_file;
    }

    /**
     * @return bool
     */
    public function should_show_update_optin() {

        // don't show if flag is not set
        if( ! get_option( $this->option_notice, false ) ) {
            return false;
        }

        // stop showing if opted-in already
        if( get_option( $this->option_enable, false ) ) {
            return false;
        }

        // only show to users with required capability
        if( ! current_user_can( self::CAPABILITY ) ) {
            return false;
        }

        return true;
    }
}