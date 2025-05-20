<?php

/**
 * Class MC4WP_Exporter
 */
class MC4WP_Personal_Data_Exporter
{
    /**
     * Registers the personal data exporter for comments.
     *
     * @param array[] $exporters An array of personal data exporters.
     * @return array[] An array of personal data exporters.
     */
    public static function add_mailchimp_to_privacy_export($exporters)
    {
        $exporters['mailchimp-subscriptions'] = [
            'exporter_friendly_name' => __('Mailchimp Subscriptions'),
            'callback' => [self::class, 'get_mailchimp_subscription_data']
        ];

        return $exporters;
    }

    /**
     * Retrieves the Mailchimp subscription data for a given email address.
     *
     * This method uses the Mailchimp for WordPress (MC4WP) API to search for members based on the provided
     * email address and returns a list of Mailchimp lists the user is subscribed to, if any.
     *
     * @param string $email_address The email address of the user to search for.
     *
     * @return array An array containing the user's Mailchimp subscription data:
     *               - 'data' (array): The subscription information, including:
     *                 - 'group_id' (string): The group identifier for Mailchimp.
     *                 - 'group_label' (string): The label for the group ('Mailchimp Subscriptions').
     *                 - 'item_id' (string): The item identifier ('mailchimp-subscriptions').
     *                 - 'data' (array): The subscription details, with:
     *                   - 'name' (string): The label ('Mailchimp List').
     *                   - 'value' (string): A comma-separated list of Mailchimp lists the user is subscribed to.
     *               - 'done' (bool): Indicates the completion of the process (always true).
     */
    public static function get_mailchimp_subscription_data($email_address)
    {
        $api = mc4wp_get_api_v3();
        $client = $api->get_client();
        $data = $client->get('search-members?query=' . urlencode($email_address));

        // Parse the API response to get the lists the user is subscribed to.
        $subscribed_lists = [];
        $data_to_export = [];

        if (!empty($data->exact_matches->members)) {
            $lists = $api->get_lists();
            foreach ($data->exact_matches->members as $member) {
                // Fetch the user's subscribed lists.
                if (isset($member->list_id)) {
                    foreach ($lists as $list) {
                        if ($list->id == $member->list_id) {
                            $subscribed_lists[] = $list->name;
                            continue;
                        }
                    }
                }
            }
        }

        if ($subscribed_lists) {
            $data_to_export[] = [
                'group_id' => 'mailchimp',
                'group_label' => __('Mailchimp Subscriptions', 'mailchimp-for-wp'),
                'item_id' => 'mailchimp-subscriptions',
                'data' => [
                    [
                        'name'  => __('Mailchimp Lists', 'mailchimp-for-wp'),
                        'value' => implode(', ', $subscribed_lists),
                    ]
                ]
            ];
        }

        return [
            'data' => $data_to_export,
            'done' => true,
        ];
    }
}
