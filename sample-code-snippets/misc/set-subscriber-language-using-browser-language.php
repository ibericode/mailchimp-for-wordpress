<?php

/*
Plugin Name: MailChimp for WordPress - Send browser language as subscriber language
Plugin URI: https://mc4wp.com/
Description: Includes the browser language in all sign-up attempts.
Author: ibericode
Version: 1.0
Author URI: https://ibericode.com/
*/

add_filter('mc4wp_subscriber_data', function (MC4WP_MailChimp_Subscriber $subscriber) {
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return $subscriber;
    }

    $subscriber->language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    return $subscriber;
});
