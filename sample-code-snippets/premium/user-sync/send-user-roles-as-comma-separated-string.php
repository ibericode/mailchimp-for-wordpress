<?php

add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user ) {
    // add to merge fields, change "mailchimp_field_name" to the name of your merge field in MailChimp.
    $subscriber->merge_fields[ 'ROLES' ] = join( ',', $user->roles );
    return $subscriber;
}, 10, 2 );
