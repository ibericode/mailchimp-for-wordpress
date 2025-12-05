<?php

/**
 * This code snippet will check if any of the CF7 fields is an array (checkbox) and convert it to a comma separated string.
 * This way it can be saved in a normal text field in your Mailchimp audience.
 *
 * In your CF7 form make sure the Checkbox name has the mc4wp- prefix, eg. mc4wp-MMERGE9
 */

add_filter('mc4wp_integration_contact-form-7_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber, $cf7_form_id) {
    foreach ($subscriber->merge_fields as $key => &$value) {
        if (is_array($value)) {
            $value = join(', ', $value);
        }
    }
    return $subscriber;
}, 10, 2);
