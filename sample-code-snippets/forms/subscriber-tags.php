<?php

/**
 * This adds the "My tag" tag to all new subscribers added by the plugin.
 *
 * The "mc4wp_subscriber_form_data" filter only runs for form requests.
 * Use "mc4wp_subscriber_data" to hook into both form & integration requests.
 */

add_filter('mc4wp_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = 'My tag';
    return $subscriber;
});
