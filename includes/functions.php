<?php

/**
 * Get a service by its name
 *
 * _Example:_
 *
 * $forms = mc4wp('forms');
 * $api = mc4wp('api');
 *
 * When no service parameter is given, the entire container will be returned.
 *
 * @param string $service (optional)
 * @throws Exception when service is not found
 * @return object
 */
function mc4wp( $service = null ) {
	static $mc4wp;

	if( ! $mc4wp ) {
		$mc4wp = new MC4WP_Container();
	}

	if( $service ) {
		return $mc4wp->get( $service );
	}

	return $mc4wp;
}

/**
 * Gets the MailChimp for WP options from the database
 * Uses default values to prevent undefined index notices.
 *
 * @since 1.0
 * @access public
 * @staticvar array $options
 * @return array
 */
function mc4wp_get_options() {
	static $options;

	if( ! $options ) {
		$defaults = require MC4WP_PLUGIN_DIR . 'config/default-settings.php';
		$options = (array) get_option( 'mc4wp', array() );
		$options = array_merge( $defaults, $options );
	}

	return $options;
}


/**
 * Gets the MailChimp for WP API class and injects it with the API key
 *
 * @staticvar $instance
 *
 * @since 1.0
 * @access public
 *
 * @return MC4WP_API
 */
function mc4wp_get_api() {
	$opts = mc4wp_get_options();
	$instance = new MC4WP_API( $opts['api_key'] );
	return $instance;
}

/**
 * Retrieves the URL of the current WordPress page
 *
 * @access public
 * @since 2.0
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
 * @access public
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
 * Guesses merge vars based on given data & current request.
 *
 * @since 3.0
 * @access public
 *
 * @param array $merge_vars
 *
 * @return array
 */
function mc4wp_guess_merge_vars( $merge_vars = array() ) {

	// maybe guess first and last name
	if ( isset( $merge_vars['NAME'] ) ) {
		if( ! isset( $merge_vars['FNAME'] ) && ! isset( $merge_vars['LNAME'] ) ) {
			$strpos = strpos( $merge_vars['NAME'], ' ' );
			if ( $strpos !== false ) {
				$merge_vars['FNAME'] = trim( substr( $merge_vars['NAME'], 0, $strpos ) );
				$merge_vars['LNAME'] = trim( substr( $merge_vars['NAME'], $strpos ) );
			} else {
				$merge_vars['FNAME'] = $merge_vars['NAME'];
			}
		}
	}

	// set ip address
	if( empty( $merge_vars['OPTIN_IP'] ) ) {
		$optin_ip = mc4wp('request')->get_client_ip();

		if( ! empty( $optin_ip ) ) {
			$merge_vars['OPTIN_IP'] = $optin_ip;
		}
	}

	/**
	 * Filters merge vars which are sent to MailChimp
	 *
	 * @param array $merge_vars
	 */
	$merge_vars = (array) apply_filters( 'mc4wp_merge_vars', $merge_vars );

	return $merge_vars;
}

/**
 * Gets the "email type" for new subscribers.
 *
 * Possible return values are either "html" or "text"
 *
 * @access public
 * @since 3.0
 *
 * @return string
 */
function mc4wp_get_email_type() {

	$email_type = 'html';

	/**
	 * Filters the email type preference for this new subscriber.
	 *
	 * @param string $email_type
	 */
	$email_type = apply_filters( 'mc4wp_email_type', $email_type );

	return $email_type;
}

/**
 *
 * @ignore
 * @return bool
 */
function __mc4wp_use_sslverify() {

	// Disable for all transports other than CURL
	if( ! function_exists( 'curl_version' ) ) {
		return false;
	}

	$curl = curl_version();

	// Disable if OpenSSL is not installed
	if( empty( $curl['ssl_version'] ) ) {
		return false;
	}

	$ssl_version = preg_replace( '/[^0-9\.]/', '', $curl['ssl_version'] );
	$required_ssl_version = '1.0.1';

	// Disable if OpenSSL is not at version 1.0.1
	if( version_compare( $ssl_version, $required_ssl_version, '<' ) ) {
		return false;
	}

	// Last character should be "f" or higher in alphabet.
	// Example: 1.0.1f
	$last_character = substr( $curl['ssl_version'], -1 );
	if( is_string( $last_character ) ) {
		if( ord( strtoupper( $last_character ) ) < ord( 'F' ) ) {
			return false;
		}
	}

	return true;
}