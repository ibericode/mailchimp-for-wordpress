<?php

/**
* Echoes a sign-up checkbox.
*/
function mc4wp_checkbox() {
	$checkbox_manager = MC4WP_Lite::instance()->get_checkbox_manager();

	// manually instantiate comment form integration class
	if( ! isset( $checkbox_manager->integrations['comment_form'] ) ) {
		$checkbox_manager->integrations['comment_form'] = new MC4WP_Comment_Form_Integration();
	}

	$checkbox_manager->integrations['comment_form']->output_checkbox();
}

/**
 * Echoes a MailChimp for WordPress form
 *
 * @param   int     $id     The form ID
 */
function mc4wp_form( $id = 0 ) {
	echo mc4wp_get_form( $id );
}

/**
* Returns HTML for sign-up form with the given $form_id.
*
* @param    int     $form_id.
* @return   string  HTML of given form_id.
*/
function mc4wp_get_form( $id = 0 ) {
	$form_manager = MC4WP_Lite::instance()->get_form_manager();
	return $form_manager->output_form( array( 'id' => $id ) );
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

/****************************~***
 *      Deprecated functions    *
 ********************************/

/**
* Echoes a sign-up form.
*
* @deprecated 1.3.1 Use mc4wp_form() instead.
* @see mc4wp_form()
*/
function mc4wp_show_form( $id = 0 ) {
	_deprecated_function( __FUNCTION__, 'MailChimp for WP v1.3.1', 'mc4wp_form' );
	mc4wp_form( $id );
}

/**
* Echoes a sign-up checkbox.
*
* @deprecated 1.3.1 Use mc4wp_checkbox() instead
* @see mc4wp_checkbox()
*/
function mc4wp_show_checkbox() {
	_deprecated_function( __FUNCTION__, 'MailChimp for WP v1.3.1', 'mc4wp_form' );
	mc4wp_checkbox();
}
