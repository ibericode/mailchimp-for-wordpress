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

	/**
	 * Filters the MailChimp for WordPress settings (general).
	 *
	 * @param array $options
	 */
	return apply_filters( 'mc4wp_settings', $options );
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
 * Creates a new instance of the Debug Log
 *
 * @return MC4WP_Debug_Log
 */
function mc4wp_get_debug_log() {

	// get default log file location
	$upload_dir = wp_upload_dir();
	$file = trailingslashit( $upload_dir['basedir'] ) . 'mc4wp-debug.log';

	/**
	 * Filters the log file to write to.
	 *
	 * @param string $file The log file location. Default: /wp-content/uploads/mc4wp-debug.log
	 */
	$file = apply_filters( 'mc4wp_debug_log_file', $file );

	/**
	 * Filters the minimum level to log messages.
	 *
	 * @see MC4WP_Debug_Log
	 *
	 * @param string|int $level The minimum level of messages which should be logged.
	 */
	$level = apply_filters( 'mc4wp_debug_log_level', 'warning' );

	return new MC4WP_Debug_Log( $file, $level );
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

	// Disable if on WP 4.4, see https://core.trac.wordpress.org/ticket/34935
	if( $GLOBALS['wp_version'] === '4.4' ) {
		return false;
	}

	return true;
}

/**
 * This will replace the first half of a string with "*" characters.
 *
 * @param string $string
 * @return string
 */
function mc4wp_obfuscate_string( $string ) {

	$length = strlen( $string );
	$obfuscated_length = ceil( $length / 2 );

	$string = str_repeat( '*', $obfuscated_length ) . substr( $string, $obfuscated_length );
	return $string;
}