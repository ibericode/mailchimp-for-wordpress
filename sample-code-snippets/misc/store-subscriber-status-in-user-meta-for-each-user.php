<?php

/*
Plugin Name: MC4WP - Store subscriber status in user meta
Plugin URI: https://mc4wp.com/
Description: Fetches subscriber status for each user from MailChimp list bc502db480
Author: Danny van Kooten
Version: 1.0
Author URI: https://mc4wp.com/
*/

if (is_admin() && isset($_GET['mc4wp-fetch-user-subscriber-status'])) {
    add_action('admin_init', function () {
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20; // TODO: Change this
        $mailchimp_list_id = isset($_GET['mailchimp_list_id']) ? $_GET['mailchimp_list_id'] : "bc502db480";

        $api = mc4wp_get_api_v3();

        $users = get_users([ 'offset' => $offset, 'number' => $limit ]);
        if (! is_array($users) || empty($users)) {
            return;
        }

        foreach ($users as $user) {
            // don't process same user more than once
            $meta_value = get_user_meta($user->ID, 'mailchimp_opted_in', true);
            if (strlen($meta_value) > 0) {
                continue;
            }

            // sleep for 0.2 seconds
            usleep(2000);

            try {
                // make remote API call
                $subscriber = $api->get_list_member($mailchimp_list_id, $user->user_email);
                $opted_in = ( $subscriber && in_array($subscriber->status, [ 'pending', 'subscribed' ]) );
            } catch (MC4WP_API_Resource_Not_Found_Exception $e) {
                $opted_in = false;
            } catch (Exception $e) {
                die($e);
            }

            // set meta value
            update_user_meta($user->ID, 'mailchimp_opted_in', $opted_in ? "1" : "0");
        }

        // redirect to same page but with higher offset to process next batch
        wp_redirect(add_query_arg([ 'offset' => $offset + $limit ]));
        exit;
    });
}
