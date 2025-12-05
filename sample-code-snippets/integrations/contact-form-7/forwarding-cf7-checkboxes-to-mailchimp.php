<?php

/**
 * This code snippet converts a checkbox field from Contact Form 7 to a comma separated string so you can store it in a text field in Mailchimp.
 *
 * In your Contact Form 7 form make sure the Checkbox name has the mc4wp- prefix, eg. mc4wp-MMERGE9
 */


add_filter('mc4wp_integration_contact-form-7_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber, $cf7_form_id) {
    $subscriber->merge_fields['MMERGE9'] = join(', ', $subscriber->merge_fields['MMERGE9'] ?? []);
    return $subscriber;
}, 10, 2);
