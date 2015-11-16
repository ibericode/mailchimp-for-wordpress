<?php

/**
 * Gets the MailChimp for WP options from the database
 * Uses default values to prevent undefined index notices.
 *
 * @return array
 */
function mc4wp_get_options() {
	$defaults = require MC4WP_PLUGIN_DIR . 'config/default-settings.php';
	$options = (array) get_option( 'mc4wp', array() );
	return array_merge( $defaults, $options );
}


/**
 * Gets the MailChimp for WP API class and injects it with the API key
 *
 * @staticvar $instance
 * @since 1.0
 * @return MC4WP_API
 */
function mc4wp_get_api() {
	static $instance;

	if( $instance instanceof MC4WP_API ) {
		return $instance;
	}

	$opts = mc4wp_get_options();
	$instance = new MC4WP_API( $opts['api_key'] );
	return $instance;
}

/**
 * Retrieves the URL of the current WordPress page
 *
 * @return  string  The current URL (escaped)
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
	$url = trailingslashit( $url );

	return esc_url( $url );
}

/**
 * Sanitizes all values in a mixed variable.
 *
 * @param mixed $value
 *
 * @return mixed
 */
function mc4wp_sanitize_deep( $value ) {

	if ( is_scalar( $value ) ) {
		$value = sanitize_text_field( $value );
	} elseif( is_array( $value ) ) {
		$value = array_map( 'mc4wp_sanitize_deep', $value );
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ( $vars as $key => $data ) {
			$value->{$key} = mc4wp_sanitize_deep( $data );
		}
	}

	return $value;
}

/**
 * @param $name
 * @param $instance
 */
function mc4wp_register_instance( $name, $instance ) {
	return MC4WP_Service_Container::instance()->register( $name, $instance );
}

/**
 * @param $name
 *
 * @return mixed
 * @throws Exception
 */
function mc4wp_get_instance( $name ) {
	return MC4WP_Service_Container::instance()->get( $name );
}

/**
 * Helper function, ensures given variable is (wrapped in) an array.
 *
 * @since 3.0
 * @param mixed $mixed
 * @return array
 */
function mc4wp_wrap_in_array( $mixed ) {
	if( is_array( $mixed ) ) {
		return $mixed;
	}

	return array( $mixed );
}