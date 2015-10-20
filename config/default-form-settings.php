<?php
return array(
	'css' => 0,
	'custom_theme_color' => '#1af',
	'ajax' => 1,
	'double_optin' => 1,
	'update_existing' => 0,
	'replace_interests' => 1,
	'send_welcome' => 0,
	'text_subscribed' => __( 'Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'mailchimp-for-wp' ),
	'text_error' => __( 'Oops. Something went wrong. Please try again later.', 'mailchimp-for-wp' ),
	'text_invalid_email' => __( 'Please provide a valid email address.', 'mailchimp-for-wp' ),
	'text_already_subscribed' => __( 'Given email address is already subscribed, thank you!', 'mailchimp-for-wp' ),
	'text_invalid_captcha' => __( 'Please complete the CAPTCHA.', 'mailchimp-for-wp' ),
	'text_required_field_missing' => __( 'Please fill in the required fields.', 'mailchimp-for-wp' ),
	'text_unsubscribed' => __( 'You were successfully unsubscribed.', 'mailchimp-for-wp' ),
	'text_not_subscribed' => __( 'Given email address is not subscribed.', 'mailchimp-for-wp' ),
	'redirect' => '',
	'hide_after_success' => 0,
	'send_email_copy' => 0,
	'lists' => array(),
	'email_copy_receiver' => get_bloginfo( 'admin_email' )
);
