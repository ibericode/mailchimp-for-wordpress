<?php

/**
 * The "mc4wp_subscriber_form_data" filter only runs for form requests.
 * Use "mc4wp_subscriber_data" to hook into both form & integration requests.
 */

//This will add the tag "My tag" to all new subscribers added by the plugin.
add_filter('mc4wp_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = 'My tag';
    return $subscriber;
});

//This will remove the tag "My tag" from all new subscribers added by the plugin.
add_filter('mc4wp_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = ['name' => 'My Tag', 'status' => 'inactive'];
    return $subscriber;
});


//You can add and remove multiple tags at once.
add_filter('mc4wp_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->tags[] = ['name' => 'My Tag', 'status' => 'active'];
    $subscriber->tags[] = ['name' => 'Another tag', 'status' => 'active'];
    $subscriber->tags[] = ['name' => 'Remove this tag', 'status' => 'inactive'];
    return $subscriber;
});
