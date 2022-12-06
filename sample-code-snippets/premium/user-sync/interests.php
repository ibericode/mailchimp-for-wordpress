<?php

/**
 * Adds the user to 2 interests
 *
 * @param MC4WP_MailChimp_Subscriber $subscriber The data that is sent to MailChimp
 * @param WP_User $user The user that is being synchronized
 *
 * @return array Our modified data array
 */
add_filter( 'mc4wp_user_sync_subscriber_data', function( \MC4WP_MailChimp_Subscriber $subscriber, \WP_User $user ) {
    $subscriber->interests[ "interest-id-1" ] = true;
    $subscriber->interests[ "interest-id-2" ] = true;
    return $subscriber;
}, 10, 2 );