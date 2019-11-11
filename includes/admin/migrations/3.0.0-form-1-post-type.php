<?php

defined( 'ABSPATH' ) or exit;

// get options
$form_options = get_option( 'mc4wp_lite_form', array() );

// bail if there are no previous options
if ( empty( $form_options ) ) {
	return;
}

// bail if there are Pro forms already
$has_forms = get_posts(
	array(
		'post_type'   => 'mc4wp-form',
		'post_status' => 'publish',
		'numberposts' => 1,
	)
);

// There are forms already, don't continue.
if ( ! empty( $has_forms ) ) {

	// delete option as it apparently exists.
	delete_option( 'mc4wp_lite_form' );
	return;
}

// create post type for form
$id = wp_insert_post(
	array(
		'post_type'    => 'mc4wp-form',
		'post_status'  => 'publish',
		'post_title'   => __( 'Default sign-up form', 'mailchimp-for-wp' ),
		'post_content' => ( empty( $form_options['markup'] ) ) ? '' : $form_options['markup'],
	)
);

// set default_form_id
update_option( 'mc4wp_default_form_id', $id );

// set form settings
$setting_keys = array(
	'css',
	'custom_theme_color',
	'double_optin',
	'update_existing',
	'replace_interests',
	'send_welcome',
	'redirect',
	'hide_after_success',
);

$settings = array();

foreach ( $setting_keys as $setting_key ) {
	// use isset to account for "0" settings
	if ( isset( $form_options[ $setting_key ] ) ) {
		$settings[ $setting_key ] = $form_options[ $setting_key ];
	}
}

// get only keys of lists setting
if ( isset( $form_options['lists'] ) ) {
	$settings['lists'] = array_keys( $form_options['lists'] );
}

update_post_meta( $id, '_mc4wp_settings', $settings );

// set form message texts
$message_keys = array(
	'text_subscribed',
	'text_error',
	'text_invalid_email',
	'text_already_subscribed',
	'text_required_field_missing',
	'text_unsubscribed',
	'text_not_subscribed',
);

foreach ( $message_keys as $message_key ) {
	if ( ! empty( $form_options[ $message_key ] ) ) {
		update_post_meta( $id, $message_key, $form_options[ $message_key ] );
	}
}

// delete old option
delete_option( 'mc4wp_lite_form' );
