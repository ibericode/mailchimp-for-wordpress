<?php

/**
 * Block certain email addresses from signing up through forms.
 */

add_filter('mc4wp_form_errors', function ($errors, MC4WP_Form $form) {
    $data = $form->get_data();
    $email = strtolower($data['EMAIL']);

    // add your blocked email addresses here.
    $blocked_emails = [
        'someemail@email.com',
        'someotheremail@email.com',
    ];

    if (in_array($email, $blocked_emails)) {
        $errors[] = 'spam';
    }

    return $errors;
}, 10, 2);
