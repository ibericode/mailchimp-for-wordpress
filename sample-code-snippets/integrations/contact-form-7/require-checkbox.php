<?php

/**
 * By default, Contact Form 7 disables HTML5 validation so we can not use the default `required` attribute.
 *
 * This code uses CF7 logic to ensure that the subscribe checkbox is checked.
 */

add_filter('wpcf7_acceptance', function ($yes) {
    if (! $yes) {
        return false;
    }
    return ! empty($_POST['_mc4wp_subscribe_contact-form-7']);
});
