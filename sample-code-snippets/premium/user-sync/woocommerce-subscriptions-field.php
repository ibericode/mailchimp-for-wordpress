<?php

// If user has subscription, set the Mailchimp field named "ACTIVE" to "Yes"
add_filter('mc4wp_user_sync_subscriber_data', function ($subscriber, $user) {

    /** @var MC4WP_MailChimp_Subscriber $subscriber */
    if (WC_Subscriptions_Manager::user_has_subscription($user->ID)) {
        $subscriber->merge_fields[ "ACTIVE" ] = 'Yes';
    } else {
        $subscriber->merge_fields[ "ACTIVE" ] = 'No';
    }

    return $subscriber;
}, 10, 2);
