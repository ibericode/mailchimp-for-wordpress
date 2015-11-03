<?php

if( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/' );
}

define( 'MC4WP_PLUGIN_DIR', __DIR__ . '/../' );

function add_filter( $hook, $callback, $prio = 10, $arguments = 1 ) {

}

function add_action( $hook, $callback, $prio = 10, $arguments = 1) {

}

function apply_filters( $hook, $value, $parameter_1 = null ) {
	return $value;
}

function is_user_logged_in() {
	return false;
}

function stripslashes_deep( $data ) {
	return $data;
}

function sanitize_text_field( $value ) {
	return $value;
}

function get_post_meta( $id, $meta_key, $single = true ) {
	return false;
}

function get_bloginfo( $key ) {
	return '';
}

function __( $string, $text_domain ) {
	return $string;
}

function get_post( $id ) {
	global $expected_post;

	if( isset( $expected_post ) ) {
		$expected_post->ID = $id;
		return $expected_post;
	}

	return mock_post( array( 'ID' => $id ) );
}

function mock_post( $data ) {
	$post = (object) array_merge(
		array(
			'ID' => 1,
			'post_type' => 'mc4wp-form',
			'post_title' => 'Form Title',
			'post_content' => ''
		),
		$data
	);

	return $post;
}

function mock_get_post( $data ) {
	global $expected_post;
	$expected_post = mock_post( $data );
}

function wp_verify_nonce( $nonce, $action ) {
	return true;
}

function is_email( $email ) {
	return true;
}