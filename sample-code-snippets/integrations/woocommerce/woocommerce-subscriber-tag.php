<?php

/**
 * This adds the "My tag" tag to all new subscribers added using WooCommerce Checkout integration.
 *
 */

add_filter('mc4wp_integration_woocommerce_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = 'My tag';
    return $subscriber;
});
