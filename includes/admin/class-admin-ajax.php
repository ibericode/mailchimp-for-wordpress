<?php

class MC4WP_Admin_Ajax
{
    /**
     * @var MC4WP_Admin_Tools
     */
    protected $tools;

    /**
     * MC4WP_Admin_Ajax constructor.
     *
     * @param MC4WP_Admin_Tools $tools
     */
    public function __construct(MC4WP_Admin_Tools $tools)
    {
        $this->tools = $tools;
    }

    /**
     * Hook AJAX actions
     */
    public function add_hooks()
    {
        add_action('wp_ajax_mc4wp_renew_mailchimp_lists', [ $this, 'refresh_mailchimp_lists' ]);
        add_action('wp_ajax_mc4wp_get_list_details', [ $this, 'get_list_details' ]);
    }

    /**
     * Empty lists cache & fetch lists again.
     */
    public function refresh_mailchimp_lists()
    {
        if (! $this->tools->is_user_authorized()) {
            wp_send_json_error();
            return;
        }

        check_ajax_referer('mc4wp-ajax');

        $mailchimp = new MC4WP_MailChimp();
        $success   = $mailchimp->refresh_lists();
        wp_send_json($success);
    }

    /**
     * Retrieve details (merge fields and interest categories) for one or multiple lists in Mailchimp
     * @throws MC4WP_API_Exception
     */
    public function get_list_details()
    {
        if (! $this->tools->is_user_authorized()) {
            wp_send_json_error();
            return;
        }

        $list_ids  = (array) explode(',', $_GET['ids']);
        $data      = [];
        $mailchimp = new MC4WP_MailChimp();
        foreach ($list_ids as $list_id) {
            $data[] = (object) [
                'id'                  => $list_id,
                'merge_fields'        => $mailchimp->get_list_merge_fields($list_id),
                'interest_categories' => $mailchimp->get_list_interest_categories($list_id),
                'marketing_permissions' => $mailchimp->get_list_marketing_permissions($list_id),
            ];
        }

        if (isset($_GET['format']) && $_GET['format'] === 'html') {
            $merge_fields          = $data[0]->merge_fields;
            $interest_categories   = $data[0]->interest_categories;
            $marketing_permissions = $data[0]->marketing_permissions;
            require MC4WP_PLUGIN_DIR . '/includes/views/parts/lists-overview-details.php';
        } else {
            wp_send_json($data);
        }
        exit;
    }
}
