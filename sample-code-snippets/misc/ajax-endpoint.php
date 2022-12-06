<?php

/**
 * The following example registers an AJAX endpoints that subscribes someone to a MailChimp list.
 *
 * Point your AJAX request at: /wp-admin/admin-ajax.php?action=my_mailchimp_subscribe
 */

add_action( 'wp_ajax_my_mailchimp_subscribe', function() {
    // get API class instance
    $api = mc4wp('api');

    $list_id = 'your-list-id-here'; // the mailchimp list to subscribe to
    $double_optin = true;           // whether to use double opt-in or not

    // get vars from POST data
    $email_address = $_POST['email'];
    $first_name = $_POST['first_name'];

    try {
        $subscriber = $api->add_list_member( $list_id, array(
            'email_address' => $email_address,
            'status' => $double_optin ? 'pending' : 'subscribed',
            'merge_fields' => array(
                'FNAME' => $first_name,
            )
        ));
    } catch( MC4WP_API_Exception $e ) {
        // an error occured
        wp_send_json_error( $e->getMessage(), $e->getCode() );
    }

    // successfully subscribed
    wp_send_json_success();
});