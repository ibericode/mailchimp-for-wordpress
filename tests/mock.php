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

/**
 * Retrieve all attributes from the shortcodes tag.
 *
 * The attributes list has the attribute name as the key and the value of the
 * attribute as the value in the key/value pair. This allows for easier
 * retrieval of the attributes, since all attributes have to be known.
 *
 * @since 2.5.0
 *
 * @param string $text
 * @return array List of attributes and their value.
 */
function shortcode_parse_atts($text) {
	$atts = array();
	$pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
	$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
	if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
		foreach ($match as $m) {
			if (!empty($m[1]))
				$atts[strtolower($m[1])] = stripcslashes($m[2]);
			elseif (!empty($m[3]))
				$atts[strtolower($m[3])] = stripcslashes($m[4]);
			elseif (!empty($m[5]))
				$atts[strtolower($m[5])] = stripcslashes($m[6]);
			elseif (isset($m[7]) && strlen($m[7]))
				$atts[] = stripcslashes($m[7]);
			elseif (isset($m[8]))
				$atts[] = stripcslashes($m[8]);
		}

		// Reject any unclosed HTML elements
		foreach( $atts as &$value ) {
			if ( false !== strpos( $value, '<' ) ) {
				if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
					$value = '';
				}
			}
		}
	} else {
		$atts = ltrim($text);
	}
	return $atts;
}
