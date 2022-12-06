<?php

add_filter( 'mc4wp_integration_woocommerce_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
    // replace "interest-id" with the actual ID of your interest.
    $subscriber->interests[ "interest-id" ] = true;

    // repeat for all interests you want to enable or disable
    // $subscriber->interests[ "91lxm10xzl" ] = true;
    // $subscriber->interests[ "91lxm10xzl" ] = false;

    return $subscriber;
});