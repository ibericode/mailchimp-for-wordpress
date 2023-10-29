<?php

add_filter( 'mc4wp_integration_ninja-forms_subscriber_data', function(MC4WP_MailChimp_Subscriber $subscriber, $form_id) {
    if ($form_id == 500) {
        $subscriber->tags[] = 'ES';
    } else if ($form_id == 510) {
        $subscriber->tags[] = 'NL';
    }
    return $subscriber;
}, 10, 2);