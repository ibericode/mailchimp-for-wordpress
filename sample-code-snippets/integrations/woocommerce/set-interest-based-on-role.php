<?php
add_filter( 'mc4wp_integration_woocommerce_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
    // get user role
    $user = wp_get_current_user();
    $user_role = array_shift( $user->roles );

    // toggle interest based on user role
    switch( $user_role ) {
        case "customer":
            $subscriber->interests[ "interest-id" ] = true;
            break;

        case "otherrole":
            $subscriber->interests[ "other-interest-id" ] = true;
            break;
    }
    
    return $subscriber;
});