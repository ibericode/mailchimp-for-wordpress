<?php
/* 
This will allow you to update a Wordpress user meta field with the Interest groups that are set in MailChimp  
when syncing data from MailChimp to Wordpress via the 2 way sync webhook.
For syncing interest groups from Wordpress to MailChimp please see: 
https://github.com/ibericode/mailchimp-for-wordpress/blob/master/sample-code-snippets/premium/user-sync/custom-field-map-setting.php
*/
add_action( 'mc4wp_user_sync_webhook', function($data, $user) {
    /* $data['merges'] contains an associative array with mailchimp field values */
    /* in this example we take the INTERESTS fields, which is a comma-separated string of interest groups */
    update_user_meta($user->ID, 'user_meta_key', $data['merges']['INTERESTS']);
}, 10, 2);
