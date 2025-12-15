<?php

/**
 * Add and or remove tags from subscribers added using WooCommerce Checkout integration.
 *
 */

//Add the tag "My tag" to all new subscribers added using WooCommerce Checkout integration.
add_filter('mc4wp_integration_woocommerce_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = 'My tag';
    return $subscriber;
});


//Remove the tag "My tag" from all new subscribers added using WooCommerce Checkout integration.
add_filter('mc4wp_integration_woocommerce_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = ['name' => 'My tag', 'status' => 'inactive'];
    return $subscriber;
});

//Add the tag "My tag" to all new subscribers added using WooCommerce Checkout integration while removing the "Remove me" tag.
add_filter('mc4wp_integration_woocommerce_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = ['name' => 'My tag', 'status' => 'active'];
    $subscriber->tags[] = ['name' => 'Remove me', 'status' => 'inactive'];
    return $subscriber;
});
