<?php


// get field data from a custom BuddyPress field.
add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user ) {

    // get meta field, change "buddypress_field_name" to the name of your BuddyPress field.
    $field_value = bp_get_profile_field_data(
        array(
            'field' => 'buddypress_field_name',
            'user_id' => $user->ID
        )
    );

    // add to merge fields, change "mailchimp_field_name" to the name of your merge field in MailChimp.
    $subscriber->merge_fields[ 'mailchimp_field_name' ] = $field_value;
    return $subscriber;
}, 10, 2 );