<?php

$default_markup = include dirname( __FILE__ ) . '/default-form.php';

return array(

	'general' => array(
		'api_key' => ''
	),

	'integrations' => array(

		// placeholder for integration specific settings ( type => array of settings )
		'custom_settings' => array(),

		// general integration settings
		'label' => __( 'Sign me up for the newsletter!', 'mailchimp-for-wp' ),
		'precheck' => 1,
		'css' => 0,
		'lists' => array(),
		'double_optin' => 1,
		'send_welcome' => 0,
		'update_existing' => 0
	),

	'form' => array(
		'css' => 0,
		'custom_theme_color' => '#1af',
		'ajax' => 1,
		'double_optin' => 1,
		'update_existing' => 0,
		'replace_interests' => 1,
		'send_welcome' => 0,
		'markup' => $default_markup,
		'lists' => array(),
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

		// comma separated string of required fields (name attributes)
		'required_fields' => 'EMAIL'
	)

);