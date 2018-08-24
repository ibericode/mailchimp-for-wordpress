<?php

/**
 * @use pl4wp_add_name_merge_vars()
 * @deprecated 4.0
 * @ignore
 *
 * @param array $merge_vars
 * @return array
 */
function pl4wp_guess_merge_vars( $merge_vars = array() ) {
	_deprecated_function( __FUNCTION__, 'PhpList for WordPress v4.0' );
	$merge_vars = pl4wp_add_name_data( $merge_vars );
	$merge_vars = _pl4wp_update_groupings_data( $merge_vars );
	return $merge_vars;
}

/**
 * Echoes a sign-up checkbox.
 *
 * @ignore
 * @deprecated 3.0
 *
 * @use pl4wp_get_integration()
 */
function pl4wp_checkbox() {
	_deprecated_function( __FUNCTION__, 'PhpList for WordPress v3.0' );
	pl4wp_get_integration('wp-comment-form')->output_checkbox();
}

/**
 * Echoes a PhpList for WordPress form
 *
 * @ignore
 * @deprecated 3.0
 * @use pl4wp_show_form()
 *
 * @param int $id
 * @param array $attributes
 *
 * @return string
 *
 */
function pl4wp_form( $id = 0, $attributes = array() ) {
	_deprecated_function( __FUNCTION__, 'PhpList for WordPress v3.0', 'pl4wp_show_form' );
	return pl4wp_show_form( $id, $attributes );
}

/**
 * @deprecated 4.1.12
 * @return string
 */
function pl4wp_get_current_url()
{
   return $pl4wp_get_current_url();
}
