<?php

add_filter('mc4wp_integration_contact-form-7_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber, $cf7_form_id) {
    if ($cf7_form_id == 500) {
        $subscriber->tags[] = 'ES';
    } elseif ($cf7_form_id == 510) {
        $subscriber->tags[] = 'NL';
    }
    return $subscriber;
}, 10, 2);
