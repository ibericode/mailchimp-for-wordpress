<?php

/**
 * Add a different tag to the subscriber based on the Ninja Forms form ID.
 * In this example Ninja Forms form with ID 500 will get the tag ES.
 * The form ID 510 will get the tag NL
 *
 * It is also possible to remove tags, see the subscriber-tags.php snippet under /forms/
 */

add_filter('mc4wp_integration_ninja-forms_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber, $form_id) {
    if ($form_id == 500) {
        $subscriber->tags[] = 'ES';
    } elseif ($form_id == 510) {
        $subscriber->tags[] = 'NL';
    }
    return $subscriber;
}, 10, 2);
