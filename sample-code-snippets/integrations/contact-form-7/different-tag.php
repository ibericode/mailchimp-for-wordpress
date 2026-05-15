<?php

/**
 * Add a different tag to the subscriber based on the CF7 form ID.
 * In this example CF7 form with ID 500 will get the tag ES.
 * The form ID 510 will get the tag NL
 *
 * NOTE: The ID in this case is the POST ID that you see in the URL when you edit a form.
 * That means the ID is always a number and not the ID you see in the CF shortcode.
 *
 * It is also possible to remove tags, see the subscriber-tags.php snippet under /forms/
 */

add_filter('mc4wp_integration_contact-form-7_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber, $cf7_form_id) {
    if ($cf7_form_id == 500) {
        $subscriber->tags[] = 'ES';
    } elseif ($cf7_form_id == 510) {
        $subscriber->tags[] = 'NL';
    }
    return $subscriber;
}, 10, 2);
