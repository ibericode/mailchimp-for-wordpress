<?php

/**
 * Set a cookie whenever someone subscribes, storing the form ID
 */
add_action( 'mc4wp_form_subscribed', function( $form ) {
    $expires = time() + 3600 * 24 * 90; // 90 days
    setcookie( 'mc4wp_subscribed', $form->ID, $expires, '/' );
});

/**
 * Prints CSS that hides a specific MailChimp for WordPress form if the "subscribed" cookie is set.
 */
add_action( 'wp_head', function() {
    if( isset( $_COOKIE['mc4wp_subscribed'] ) && empty( $_POST['_mc4wp_form_id'] ) )  {
        $form_id = (int) $_COOKIE['mc4wp_subscribed'];
        ?>
        <style type="text/css">
            .mc4wp-form-<?php echo $form_id; ?> {
                display: none !important;
            }
        </style>
        <?php
    }
}, 70 );
