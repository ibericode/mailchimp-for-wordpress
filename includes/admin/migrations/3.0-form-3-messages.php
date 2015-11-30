<?php
defined( 'ABSPATH' ) or exit;

// find all form posts
$posts = get_posts(
	array(
		'post_type' => 'mc4wp-form',
		'post_status' => 'publish',
		'numberposts' => -1
	)
);

// set form message texts
$message_keys = array(
	'text_subscribed',
	'text_error',
	'text_invalid_email',
	'text_already_subscribed',
	'text_required_field_missing',
	'text_unsubscribed',
	'text_not_subscribed'
);

foreach( $posts as $post ) {

	$settings = get_post_meta( $post->ID, '_mc4wp_settings', true );

	foreach( $message_keys as $key ) {
		if( empty( $settings[ $key ] ) ) {
			continue;
		}

		$message = $settings[ $key ];

		// move message setting over to post meta
		update_post_meta( $post->ID, $key, $message );
		unset( $settings[ $key ] );
	}

	// update post meta with unset message keys
	update_post_meta( $post->ID, '_mc4wp_settings', $settings );
}