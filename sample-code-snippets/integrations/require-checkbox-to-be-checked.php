<?php

/**
 * Adds "required" attribute to the HTML for the sign-up checkbox.
 *
 * This forces the checkbox to be checked.
 *
 * @param array $attributes
 * @param MC4WP_Integration $integration
 * @return array
 */
add_filter( 'mc4wp_integration_checkbox_attributes', function( array $attributes, $integration ) {
	$attributes['required'] = 'required';
	return $attributes;
}, 10, 2);

/**
 * If you're running Contact Form 7, please also include the following line to enable HTML5 validation.
 */
add_filter( 'wpcf7_form_novalidate', '__return_false' );