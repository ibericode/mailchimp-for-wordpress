<?php

/**
 * This snippet allows you to manually add an email address to a Mailchimp list.
 */

$api = mc4wp_get_api_v3();
$mailchimp_list_id = 'your-list-id-here'; // the mailchimp list to subscribe to
$use_double_optin = true;           // whether to use double opt-in or not
$email_address = 'johndoe@email.com';
$merge_fields = array(
	'FNAME' => 'John',
);

try {
	$subscriber = $api->add_list_member( $mailchimp_list_id, array(
		'email_address' => $email_address,
		'status' => $use_double_optin ? 'pending' : 'subscribed',
		'merge_fields' => $merge_fields,
	));
} catch( \MC4WP_API_Exception $e ) {
	// an error occured
	// you can handle it here by inspecting the expection object and removing the line bwlo
	throw $e;
}
