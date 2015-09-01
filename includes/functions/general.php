<?php

/**
* Gets the MailChimp for WP options from the database
* Uses default values to prevent undefined index notices.
*
* @param string $key
* @return array
*/
function mc4wp_get_options( $key = '' ) {
	static $defaults;
	if( is_null( $defaults ) ) {
		$email_label = __( 'Email address', 'mailchimp-for-wp' );
		$email_placeholder = __( 'Your email address', 'mailchimp-for-wp' );
		$signup_button = __( 'Sign up', 'mailchimp-for-wp' );

		$defaults = array(
			'general' => array(
				'api_key' => '',
			),
			'checkbox' => array(
				'label' => __( 'Sign me up for the newsletter!', 'mailchimp-for-wp' ),
				'precheck' => 1,
				'css' => 1,
				'show_at_comment_form' => 0,
				'show_at_registration_form' => 0,
				'show_at_multisite_form' => 0,
				'show_at_buddypress_form' => 0,
				'show_at_bbpress_forms' => 0,
				'show_at_woocommerce_checkout' => 0,
				'show_at_edd_checkout' => 0,
				'lists' => array(),
				'double_optin' => 1,
				'update_existing' => 0,
				'replace_interests' => 1,
				'send_welcome' => 0,
				'woocommerce_position' => 'order',
			),
			'form' => array(
				'css' => 'default',
				'markup' => "<p>\n\t<label>{$email_label}: </label>\n\t<input type=\"email\" name=\"EMAIL\" placeholder=\"{$email_placeholder}\" required />\n</p>\n\n<p>\n\t<input type=\"submit\" value=\"{$signup_button}\" />\n</p>",
				'text_subscribed' => __( 'Thank you, your sign-up request was successful! Please check your e-mail inbox.', 'mailchimp-for-wp' ),
				'text_error' => __( 'Oops. Something went wrong. Please try again later.', 'mailchimp-for-wp' ),
				'text_invalid_email' => __( 'Please provide a valid email address.', 'mailchimp-for-wp' ),
				'text_already_subscribed' => __( 'Given email address is already subscribed, thank you!', 'mailchimp-for-wp' ),
				'text_invalid_captcha' => __( 'Please complete the CAPTCHA.', 'mailchimp-for-wp' ),
				'text_required_field_missing' => __( 'Please fill in the required fields.', 'mailchimp-for-wp' ),
				'text_unsubscribed' => __( 'You were successfully unsubscribed.', 'mailchimp-for-wp' ),
				'text_not_subscribed' => __( 'Given email address is not subscribed.', 'mailchimp-for-wp' ),
				'redirect' => '',
				'lists' => array(),
				'hide_after_success' => 0,
				'double_optin' => 1,
				'update_existing' => 0,
				'replace_interests' => 1,
				'send_welcome' => 0,
			),
		);
	}

	$db_keys_option_keys = array(
		'mc4wp_lite' => 'general',
		'mc4wp_lite_checkbox' => 'checkbox',
		'mc4wp_lite_form' => 'form',
	);

	$options = array();
	foreach ( $db_keys_option_keys as $db_key => $option_key ) {
		$option = (array) get_option( $db_key, array() );
		$options[$option_key] = array_merge( $defaults[$option_key], $option );
	}
	
	if( '' !== $key ) {
		return $options[$key];
	}

	return $options;
}

/**
* Gets the MailChimp for WP API class and injects it with the given API key
* @since 1.0
* @return MC4WP_API
*/
function mc4wp_get_api() {
	return MC4WP_Lite::instance()->get_api();
}

/**
 * Check whether a form was submitted
 *
 * @since 2.3.8
 * @param int $form_id The ID of the form you want to check. (optional)
 * @param string $element_id The ID of the form element you want to check, eg id="mc4wp-form-1" (optional)
 * @return boolean
 */
function mc4wp_form_is_submitted( $form_id = 0, $element_id = null ) {
	$form = MC4WP_Form::get();

	if( ! $form instanceof MC4WP_Form ) {
		return false;
	}

	return $form->is_submitted( $element_id );
}

/**
 * @since 2.3.8
 * @param int $form_id
 * @return string
 */
function mc4wp_form_get_response_html( $form_id = 0 ) {
	$form = MC4WP_Form::get();

	// return empty string if form isn't submitted.
	if( ! $form instanceof MC4WP_Form || ! $form->is_submitted() ) {
		return '';
	}

	return $form->request->get_response_html();
}