<?php

/**
 * This hooks into the data that is sent to MailChimp.
 *
 * It will then take an ACF field "bar" and send it to the MailChimp "FOO" field.
 */
add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user ) {

    // MailChimp field name: FOO
    // ACF field name: bar
    $subscriber->merge_fields['FOO'] = get_field( 'bar', 'user_' . $user->ID );

    return $subscriber;
}, 10, 2 );
