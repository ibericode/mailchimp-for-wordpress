<?php

// The snippet below instructs User Sync  to include the various WooCommerce billing address fields
// And send them to a field named "ADDRESS" in Mailchimp

add_filter('mc4wp_user_sync_subscriber_data', function (\MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user) {
    // change ADDRESS to the name of your Mailchimp field with type "address"
    $subscriber->merge_fields['ADDRESS'] = [
        'addr1' => $user->billing_address_1,
        'city' => $user->billing_city,
        'state' => $user->billing_state,
        'zip' => $user->billing_postcode,
        'country' => $user->billing_country,
    ];
    return $subscriber;
}, 10, 2);
