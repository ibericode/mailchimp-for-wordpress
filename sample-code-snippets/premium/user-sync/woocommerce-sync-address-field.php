<?php

// The snippet below instructs User Sync  to include the various WooCommerce billing address fields
// And send them to a field named "ADDRESS" in Mailchimp

add_filter('mc4wp_user_sync_subscriber_data', function (\MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user) {
    // change ADDRESS to the name of your Mailchimp field with type "address"
    $subscriber->merge_fields['ADDRESS'] = [
        'addr1' => $value = get_user_meta( $user->ID, 'billing_address_1', true ),
        'city' => $value = get_user_meta( $user->ID, 'billing_city', true ),
        'state' => $value = get_user_meta( $user->ID, 'billing_state', true ),
        'zip' => $value = get_user_meta( $user->ID, 'billing_postcode', true ),
        'country' => $value = get_user_meta( $user->ID, 'billing_country', true ),
    ];
    return $subscriber;
}, 10, 2);
