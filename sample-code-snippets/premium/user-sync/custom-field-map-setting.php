<?php
//Replace _meta_key_for_storing_interest_field with the user meta field in your Wordpress database 
//This will maps the Interest groups set in Wordpress to the Interest groups in MailChimp when sending data to MailChimp.
//If you use 2 way sync you also want to look at this: https://github.com/ibericode/mc4wp-snippets/blob/master/premium/user-sync/webhook-update-user-meta-field.php
add_filter( 'mc4wp_user_sync_settings', function($settings) {
    $settings['field_map'][] = array(
        'mailchimp_field' => 'INTERESTS',
        'user_field' => '_meta_key_for_storing_interest_field',
	);
	return $settings;
});
