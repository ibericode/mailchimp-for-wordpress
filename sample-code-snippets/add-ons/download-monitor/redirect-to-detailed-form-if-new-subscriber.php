<?php

/**
* The snippet below hooks into the form with ID 500
*
* - If a subscriber was updated; it redirects straight to the download monitor download
* - If a subscriber was newly added; it redirects to another page where you can show another more detailed form. Make sure to set the "redirect on success" setting of the detailed form to your download.
*/

add_action('mc4wp_form_updated_subscriber', function ($form) {
    if ($form->ID != 500) {
        return;
    }

    dlm_mailchimp_set_cookie($form->data['EMAIL']);

   // form was used to update a subscriber; redirect to download
    wp_redirect('http://my-site.com/download-url');
    exit;
});

add_action('mc4wp_form_subscribed', function ($form) {
    if ($form->ID != 500) {
        return;
    }

   // form was used to add a new subscriber; redirect to detailed form
    wp_redirect('http://my-site.com/page-with-detailed-form');
    exit;
});
