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
 * @ignore
 * @access private
 *
 * @param string $service (optional)
 * @return mixed
 *
 * @throws Exception when service is not found
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
 * @static array $options
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
 * Gets the MailChimp for WP API class (v3) and injects it with the API key
 *
 * @since 4.0
 * @access public
 *
 * @return MC4WP_API_v3
 */
function mc4wp_get_api_v3() {
	$opts = mc4wp_get_options();
	$instance = new MC4WP_API_v3( $opts['api_key'] );
	return $instance;
}

/**
 * Gets the MailChimp for WP API class and injects it with the API key
 *
 * @deprecated 4.0
 * @use mc4wp_get_api_v3
 *
 * @since 1.0
 * @access public
 *
 * @return MC4WP_API
 */
function mc4wp_get_api() {
	_deprecated_function( __FUNCTION__, '4.0', 'mc4wp_get_api_v3' );
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
 *
 * @since 4.0
 * @ignore
 *
 * @param array $data
 * @return array
 */
function __mc4wp_update_groupings_data( $data = array() ) {

    // data still has old "GROUPINGS" key?
	if( empty( $data['GROUPINGS'] ) ) {
        return $data;
    }

    // prepare new key
    if( ! isset( $data['INTERESTS'] ) ) {
        $data['INTERESTS'] = array();
    }

    $map = get_option( 'mc4wp_groupings_map', array() );

    foreach( $data['GROUPINGS'] as $grouping_id => $groups ) {

        // for compatibility with expanded grouping arrays
        $grouping_key = $grouping_id;
        if( is_array( $groups ) && isset( $groups['id'] ) && isset( $groups['groups'] ) ) {
            $grouping_id = $groups['id'];
            $groups = $groups['groups'];
        }

        // do we have transfer data for this grouping id?
        if( ! isset( $map[ $grouping_id ] ) ) {
            continue;
        }

        // if we get a string, explode on delimiter(s)
        if( is_string( $groups ) ) {
            // for BC with 3.x: explode on comma's
            $groups = join( '|', explode(',', $groups ) );

            // explode on current delimiter
            $groups = explode( '|', $groups );
        }

        // loop through groups and find interest ID
        $migrated = 0;
        foreach( $groups as $key => $group_name_or_id ) {

            // do we know the new interest ID?
            if( empty( $map[ $grouping_id ]['groups'][ $group_name_or_id ] ) ) {
                continue;
            }

            $interest_id = $map[ $grouping_id ]['groups'][ $group_name_or_id ];

            // add to interests data
            if( ! in_array( $interest_id, $data['INTERESTS'] ) ) {
                $migrated++;
                $data['INTERESTS'][] = $interest_id;
            }
        }
        
        // remove old grouping ID if we migrated all groups.
        if( $migrated === count( $groups ) ) {
            unset( $data['GROUPINGS'][$grouping_key] );
        }
    }

	// if everything went well, this is now empty & moved to new INTERESTS key.
	if( empty( $data['GROUPINGS'] ) ) {
		unset( $data['GROUPINGS'] );
	}

	// is this empty? just unset it then.
	if( empty( $data['INTERESTS'] ) ) {
	    unset( $data['INTERESTS'] );
    }
	
	return $data;
}

/**
 * Guesses merge vars based on given data & current request.
 *
 * @since 3.0
 * @access public
 *
 * @param array $data
 *
 * @return array
 */
function mc4wp_add_name_data( $data = array() ) {

	// Guess first and last name
	if ( ! empty( $data['NAME'] ) && empty( $data['FNAME'] ) && empty( $data['LNAME'] ) ) {
        $data['NAME'] = trim( $data['NAME'] );
		$strpos = strpos( $data['NAME'], ' ' );

		if ( $strpos !== false ) {
			$data['FNAME'] = trim( substr( $data['NAME'], 0, $strpos ) );
			$data['LNAME'] = trim( substr( $data['NAME'], $strpos ) );
		} else {
			$data['FNAME'] = $data['NAME'];
		}
	}

	// Set name value
	if( empty( $data['NAME'] ) && ! empty( $data['FNAME'] ) && ! empty( $data['LNAME'] ) ) {
		$data['NAME'] = sprintf( '%s %s', $data['FNAME'], $data['LNAME'] );
	}
	
	return $data;
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
	$email_type = (string) apply_filters( 'mc4wp_email_type', $email_type );

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