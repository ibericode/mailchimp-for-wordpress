<?php

/**
 * This will send an email for every API error that occurs on forms.
 */

add_action('mc4wp_form_api_error', function ($form, $error_message) {
    // email variables
    $email_to = 'email@email.com';
    $email_subject = 'Form API failure';
    $email_message = sprintf('Form %d encountered a MailChimp API error: %s', $form->ID, $error_message);

    // send the email
    wp_mail($email_to, $email_subject, $email_message);
}, 10, 2);
