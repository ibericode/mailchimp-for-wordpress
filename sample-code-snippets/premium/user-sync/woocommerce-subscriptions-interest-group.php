<?php

// If user has subscription with ID 100 add the user to interest-id-1, once the subscription expires, remove the user from that interest group.
// Replace the '100' and "interest-id-1" with your own subscription ID and interest group ID
add_filter('mc4wp_user_sync_subscriber_data', function (\MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user) {
    if (\WC_Subscriptions_Manager::user_has_subscription($user->ID, '100', 'active')) {
        $subscriber->interests[ "interest-id-1" ] = true;
    } else {
        $subscriber->interests[ "interest-id-1" ] = false;
    }

    return $subscriber;
}, 10, 2);
