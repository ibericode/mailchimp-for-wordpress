<?php

/**
 * This will send an email every time a MailChimp for WordPress integration is successfully used to subscribe.
 */

add_action('mc4wp_integration_subscribed', function ($integration, $email_address, $merge_vars) {
    // email variables
    $email_to = 'email@email.com';
    $email_subject = 'Someone subscribed through an integration';
    $email_message = sprintf('Integration %s used by %s', $integration->name, $email_address);

    // send the email
    wp_mail($email_to, $email_subject, $email_message);
}, 10, 3);
