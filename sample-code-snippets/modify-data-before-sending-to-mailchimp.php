<?php

/**
 * Change the data that is sent to MailChimp.
 *
 * @param MC4WP_MailChimp_Subscriber $subscriber
 * @return MC4WP_MailChimp_Subscriber
 */
function myprefix_subscriber_data(MC4WP_MailChimp_Subscriber $subscriber)
{

    // add merge field
    $subscriber->merge_fields['FIELD'] = 'Value';

    // toggle interest
    $subscriber->interests["some-interest-id"] = true;

    // set language
    $subscriber->language = 'nl';

    return $subscriber;
}

// all sign-up methods
add_filter('mc4wp_subscriber_data', 'myprefix_subscriber_data');

// forms only
add_filter('mc4wp_form_subscriber_data', 'myprefix_subscriber_data');

// integrations only
add_filter('mc4wp_integration_subscriber_data', 'myprefix_subscriber_data');

// specific integration: woocommerce
add_filter('mc4wp_integration_woocommerce_subscriber_data', 'myprefix_subscriber_data');
