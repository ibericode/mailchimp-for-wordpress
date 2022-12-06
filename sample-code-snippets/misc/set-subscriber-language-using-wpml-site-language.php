<?php
/*
Plugin Name: MailChimp for WordPress - Send WPML language
Plugin URI: https://mc4wp.com/
Description: Sets the current WPML language for each sign-up attempt.
Author: ibericode
Version: 1.0
Author URI: https://ibericode.com/
*/

add_filter( 'mc4wp_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
    // do nothing if WPML is not activated
    if( ! defined( 'ICL_LANGUAGE_CODE' ) ) {
        return $subscriber;
    }

    $subscriber->language = substr( ICL_LANGUAGE_CODE, 0, 2 );
    return $subscriber;
});