<?php

// The problem with WPForms forms in this regard is that it generates field names that aren't always easy for our plugin to recognize.
// You will have to inspect the source of the form to find field names (name="...") for each field and manually map them as such:

add_filter('mc4wp_integration_wpforms_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    $subscriber->merge_fields[ "FNAME" ] = sanitize_text_field($_POST['wpforms[fields][7][first]']);
    $subscriber->merge_fields[ "COUNTRY" ] = sanitize_text_field($_POST['wpforms[fields][7][country]']);
    return $subscriber;
});
