<?php

/**
 * This hooks into the data that is sent to Mailchimp by User Sync.
 *
 * It sends the WordPress username (user_login) to a Mailchimp field named WP_USERNAME.
 * Make sure to create an audience field with the tag WP_USERNAME in Mailchimp first.
 */

add_filter('mc4wp_user_sync_subscriber_data', function ($subscriber, $user) {

    /** @var MC4WP_MailChimp_Subscriber $subscriber */
    /** @var WP_User $user */
    $subscriber->merge_fields['WP_USERNAME'] = $user->user_login;

    return $subscriber;
}, 10, 2);
