<?php

/**
 * Echoes a sign-up checkbox.
 *
 * @since 1.0
 * @deprecated 3.0
 *
*/
function mc4wp_checkbox() {
	_deprecated_function( __FUNCTION__, 'MailChimp for WordPress v3.0' );
}

/**
 * Echoes a MailChimp for WordPress form
 *
 * @param   int     $id     The form ID
 * @since 1.0
 * @deprecated 3.0
 */
function mc4wp_form( $id = 0 ) {
	_deprecated_function( __FUNCTION__, 'MailChimp for WordPress v3.0' );
}

/**
* Returns a Form instance
*
* @param    int     $form_id.
* @return   MC4WP_Form
*/
function mc4wp_get_form( $form_id = 0 ) {
	return MC4WP_Form::get_instance( $form_id );
}

/**
 * Retrieves the URL of the current WordPress page
 *
 * @return  string  The current URL, escaped for safe usage inside attributes.
 */
function mc4wp_get_current_url() {

	global $wp;

	// get requested url from global $wp object
	$site_request_uri = $wp->request;

	// fix for IIS servers using index.php in the URL
	if( false !== stripos( $_SERVER['REQUEST_URI'], '/index.php/' . $site_request_uri ) ) {
		$site_request_uri = 'index.php/' . $site_request_uri;
	}

	// concatenate request url to home url
	$url = home_url( $site_request_uri );

	// add trailing slash, if necessary
	if( substr( $_SERVER['REQUEST_URI'] , -1 ) === '/' ) {
		$url = trailingslashit( $url );
	}

	return esc_url( $url );
}