<?php

/**
 * Set a cookie whenever someone subscribes
 */
add_action( 'mc4wp_form_subscribed', function() {
    setcookie( 'mc4wp_subscribed', 1, time() + 3600 * 24 * 90, '/' );
});

/**
 * Prints CSS that hides the MailChimp for WordPress form if the "subscribed" cookie is set.
 */
add_action( 'wp_head', function() {
    if( isset( $_COOKIE['mc4wp_subscribed'] ) && empty( $_POST['_mc4wp_form_id'] ) )  {
        ?>
        <style type="text/css">
            .mc4wp-form {
                display: none !important;
            }
        </style>
        <?php
    }
}, 70 );