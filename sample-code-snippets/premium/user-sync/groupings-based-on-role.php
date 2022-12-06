<?php

/**
 * Add or remove users with the role "role_1" to the interest group with the ID interest-group-id and "role_2" to interest id "other-interest-group-id".
 */
add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user ) {
    $subscriber->interests[ 'interest-group-id' ] = in_array( 'role_1', $user->roles );
    $subscriber->interests[ 'other-interest-group-id' ] = in_array( 'role_2', $user->roles );
	//you can repeat this line for more groups / roles.

    return $subscriber;
}, 14, 2 );


/**
 * This is another way to do the same thing, for the role "subscriber".
 */
add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user ) {
    // toggle interest ID based on user role
    if( in_array( 'subscriber', $user->roles ) ) {
        $subscriber->interests[ "interest-id-members" ] = true;
    } else {
        $subscriber->interests[ "interest-id-members" ] = false;
    }

	return $subscriber;
}, 10, 2 );

/**
 * A simple example how to include your own function in a Wordpress theme for setting Interest group. 
 * The code below is not needed for the code above to work.
 */
add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber ) {
    // do nothing if user is logged in
    if( is_user_logged_in() ) {
        return $subscriber;
    }

    // toggle the interest here, by ID.
    // you can find this ID by going to MailChimp for WP > MailChimp > List Overview
    $subscriber->interests[ "interest-id" ] = true;
    return $subscriber;
} );
