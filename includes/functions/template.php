<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


/**
* Echoes a sign-up checkbox.
*/
function mc4wp_checkbox() {
	global $mc4wp;

	// manually instantiate comment form integration class
	if( ! isset( $mc4wp->get_checkbox_manager()->integrations['comment_form'] ) ) {
		$mc4wp->get_checkbox_manager()->integrations['comment_form'] = new MC4WP_Comment_Form_Integration();
	}

	$mc4wp->get_checkbox_manager()->integrations['comment_form']->output_checkbox();
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
	global $mc4wp;
	return $mc4wp->get_form_manager()->form( array( 'id' => $id ) );
}


/**
* Returns text with {variables} replaced.
*
* @param    string  $text
* @param    array   $list_ids   Array of list id's
* @return   string  $text       The text with {variables} replaced.
*/
function mc4wp_replace_variables( $text, $list_ids = array() ) {

	// get current WPML language or general site language
	$language = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_locale();

	// replace general vars
	$needles = array( '{ip}', '{current_url}', '{date}', '{time}', '{language}' );
	$replacements = array( $_SERVER['REMOTE_ADDR'], mc4wp_get_current_url(), date( 'm/d/Y' ), date( 'H:i:s' ), $language );
	$text = str_ireplace( $needles, $replacements, $text );

	// subscriber count? only fetch these if the tag is actually used
	if ( stristr( $text, '{subscriber_count}' ) !== false ) {
		$mailchimp = new MC4WP_MailChimp();
		$subscriber_count = $mailchimp->get_subscriber_count( $list_ids );
		$text = str_ireplace( '{subscriber_count}', $subscriber_count, $text );
	}

	// replace {email} tag
	if( isset( $_GET['mc4wp_email'] ) ) {
		$email = esc_attr( $_GET['mc4wp_email'] );
	} elseif( isset( $_COOKIE['mc4wp_email'] ) ) {
		$email = esc_attr( $_COOKIE['mc4wp_email'] );
	} else {
		$email = '';
	}

	$text = str_ireplace( '{email}', $email, $text );

	// replace user variables
	$needles = array( '{user_email}', '{user_firstname}', '{user_lastname}', '{user_name}', '{user_id}' );
	if ( is_user_logged_in() && ( $user = wp_get_current_user() ) && ( $user instanceof WP_User ) ) {
		// logged in user, replace vars by user vars
		$replacements = array( $user->user_email, $user->first_name, $user->last_name, $user->display_name, $user->ID );
		$text = str_replace( $needles, $replacements, $text );
	} else {
		// no logged in user, replace vars with empty string
		$text = str_replace( $needles, '', $text );
	}

	return $text;
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
