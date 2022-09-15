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

	if ( ! $mc4wp ) {
		$mc4wp = new MC4WP_Container();
	}

	if ( $service ) {
		return $mc4wp->get( $service );
	}

	return $mc4wp;
}

/**
 * Gets the Mailchimp for WP options from the database
 * Uses default values to prevent undefined index notices.
 *
 * @since 1.0
 * @access public
 * @static array $options
 * @return array
 */
function mc4wp_get_options() {
	$defaults = require MC4WP_PLUGIN_DIR . '/config/default-settings.php';
	$options  = (array) get_option( 'mc4wp', array() );
	$options  = array_merge( $defaults, $options );

	/**
	 * Filters the Mailchimp for WordPress settings (general).
	 *
	 * @param array $options
	 */
	return apply_filters( 'mc4wp_settings', $options );
}

/**
 * @return array
 */
function mc4wp_get_settings() {
	return mc4wp_get_options();
}

/**
 * @since 4.2.6
 * @return string
 */
function mc4wp_get_api_key() {
	// try to get from constant
	if ( defined( 'MC4WP_API_KEY' ) && constant( 'MC4WP_API_KEY' ) !== '' ) {
		return MC4WP_API_KEY;
	}

	// get from options
	$opts = mc4wp_get_options();
	return $opts['api_key'];
}

/**
 * Gets the Mailchimp for WP API class (v3) and injects it with the API key
 *
 * @since 4.0
 * @access public
 *
 * @return MC4WP_API_V3
 */
function mc4wp_get_api_v3() {
	$api_key  = mc4wp_get_api_key();
	$instance = new MC4WP_API_V3( $api_key );
	return $instance;
}

/**
 * Gets the Mailchimp for WP API class and injects it with the API key
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
	$api_key  = mc4wp_get_api_key();
	$instance = new MC4WP_API( $api_key );
	return $instance;
}

/**
 * Creates a new instance of the Debug Log
 *
 * @return MC4WP_Debug_Log
 */
function mc4wp_get_debug_log() {
	$opts = mc4wp_get_options();

	// get default log file location
	$upload_dir = wp_upload_dir( null, false );
	$file       = $upload_dir['basedir'] . '/mailchimp-for-wp/debug-log.php';
	$default_file = $file;

	/**
	 * Filters the log file to write to.
	 *
	 * @param string $file The log file location. Default: /wp-content/uploads/mailchimp-for-wp/mc4wp-debug.log
	 */
	$file = apply_filters( 'mc4wp_debug_log_file', $file );

	if ( $file === $default_file ) {
		$dir = dirname( $file );
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}

		if ( ! is_file( $dir . '/.htaccess' ) ) {
			$lines = array(
				'<IfModule !authz_core_module>',
				'Order deny,allow',
				'Deny from all',
				'</IfModule>',
				'<IfModule authz_core_module>',
				'Require all denied',
				'</IfModule>',
			);
			file_put_contents( $dir . '/.htaccess', join( PHP_EOL, $lines ) );
		}

		if ( ! is_file( $dir . '/index.html' ) ) {
			file_put_contents( $dir . '/index.html', '' );
		}
	}

	/**
	 * Filters the minimum level to log messages.
	 *
	 * @see MC4WP_Debug_Log
	 *
	 * @param string|int $level The minimum level of messages which should be logged.
	 */
	$level = apply_filters( 'mc4wp_debug_log_level', $opts['debug_log_level'] );

	return new MC4WP_Debug_Log( $file, $level );
}

/**
 * Get URL to a file inside the plugin directory
 *
 * @since 4.8.3
 * @param string $path
 * @return string
 */
function mc4wp_plugin_url( $path ) {
	static $base = null;
	if ( $base === null ) {
		$base = plugins_url( '/', MC4WP_PLUGIN_FILE );
	}

	return $base . $path;
}


/**
 * Get current URL (full)
 *
 * @return string
 */
function mc4wp_get_request_url() {
	global $wp;

	// get requested url from global $wp object
	$site_request_uri = $wp->request;

	// fix for IIS servers using index.php in the URL
	if ( false !== stripos( $_SERVER['REQUEST_URI'], '/index.php/' . $site_request_uri ) ) {
		$site_request_uri = 'index.php/' . $site_request_uri;
	}

	// concatenate request url to home url
	$url = home_url( $site_request_uri );
	$url = trailingslashit( $url );

	return esc_url( $url );
}

/**
 * Get current URL path.
 *
 * @return string
 */
function mc4wp_get_request_path() {
	return $_SERVER['REQUEST_URI'];
}

/**
* Get IP address for client making current request
*
* @return string|null
*/
function mc4wp_get_request_ip_address() {
	if ( isset( $_SERVER['X-Forwarded-For'] ) ) {
		$ip_address = $_SERVER['X-Forwarded-For'];
	} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		return $_SERVER['REMOTE_ADDR'];
	}

	if ( isset( $ip_address ) ) {
		if ( ! is_array( $ip_address ) ) {
			$ip_address = explode( ',', $ip_address );
		}

		// use first IP in list
		$ip_address = trim( $ip_address[0] );

		// if IP address is not valid, simply return null
		if ( ! filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
			return null;
		}

		return $ip_address;
	}

	return null;
}

/**
 * Strips all HTML tags from all values in a mixed variable, then trims the result.
 *
 * @access public
 * @param mixed $value
 *
 * @return mixed
 */
function mc4wp_sanitize_deep( $value ) {
	if ( is_scalar( $value ) ) {
		// strip all HTML tags & whitespace
		$value = trim( strip_tags( $value ) );

		// convert &amp; back to &
		$value = html_entity_decode( $value, ENT_NOQUOTES );
	} elseif ( is_array( $value ) ) {
		$value = array_map( 'mc4wp_sanitize_deep', $value );
	} elseif ( is_object( $value ) ) {
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
function _mc4wp_update_groupings_data( $data = array() ) {

	// data still has old "GROUPINGS" key?
	if ( empty( $data['GROUPINGS'] ) ) {
		return $data;
	}

	// prepare new key
	if ( ! isset( $data['INTERESTS'] ) ) {
		$data['INTERESTS'] = array();
	}

	$map = get_option( 'mc4wp_groupings_map', array() );

	foreach ( $data['GROUPINGS'] as $grouping_id => $groups ) {

		// for compatibility with expanded grouping arrays
		$grouping_key = $grouping_id;
		if ( is_array( $groups ) && isset( $groups['id'] ) && isset( $groups['groups'] ) ) {
			$grouping_id = $groups['id'];
			$groups      = $groups['groups'];
		}

		// do we have transfer data for this grouping id?
		if ( ! isset( $map[ $grouping_id ] ) ) {
			continue;
		}

		// if we get a string, explode on delimiter(s)
		if ( is_string( $groups ) ) {
			// for BC with 3.x: explode on comma's
			$groups = join( '|', explode( ',', $groups ) );

			// explode on current delimiter
			$groups = explode( '|', $groups );
		}

		// loop through groups and find interest ID
		$migrated = 0;
		foreach ( $groups as $key => $group_name_or_id ) {

			// do we know the new interest ID?
			if ( empty( $map[ $grouping_id ]['groups'][ $group_name_or_id ] ) ) {
				continue;
			}

			$interest_id = $map[ $grouping_id ]['groups'][ $group_name_or_id ];

			// add to interests data
			if ( ! in_array( $interest_id, $data['INTERESTS'], false ) ) {
				$migrated++;
				$data['INTERESTS'][] = $interest_id;
			}
		}

		// remove old grouping ID if we migrated all groups.
		if ( $migrated === count( $groups ) ) {
			unset( $data['GROUPINGS'][ $grouping_key ] );
		}
	}

	// if everything went well, this is now empty & moved to new INTERESTS key.
	if ( empty( $data['GROUPINGS'] ) ) {
		unset( $data['GROUPINGS'] );
	}

	// is this empty? just unset it then.
	if ( empty( $data['INTERESTS'] ) ) {
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
		$strpos       = strpos( $data['NAME'], ' ' );

		if ( $strpos !== false ) {
			$data['FNAME'] = trim( substr( $data['NAME'], 0, $strpos ) );
			$data['LNAME'] = trim( substr( $data['NAME'], $strpos ) );
		} else {
			$data['FNAME'] = $data['NAME'];
		}
	}

	// Set name value
	if ( empty( $data['NAME'] ) && ! empty( $data['FNAME'] ) && ! empty( $data['LNAME'] ) ) {
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
function _mc4wp_use_sslverify() {

	// Disable for all transports other than CURL
	if ( ! function_exists( 'curl_version' ) ) {
		return false;
	}

	$curl = curl_version();

	// Disable if OpenSSL is not installed
	if ( empty( $curl['ssl_version'] ) ) {
		return false;
	}

	// Disable if on WP 4.4, see https://core.trac.wordpress.org/ticket/34935
	if ( $GLOBALS['wp_version'] === '4.4' ) {
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
	$length            = strlen( $string );
	$obfuscated_length = ceil( $length / 2 );
	$string            = str_repeat( '*', $obfuscated_length ) . substr( $string, $obfuscated_length );
	return $string;
}

/**
 * @internal
 * @ignore
 */
function _mc4wp_obfuscate_email_addresses_callback( $m ) {
	$one   = $m[1] . str_repeat( '*', strlen( $m[2] ) );
	$two   = $m[3] . str_repeat( '*', strlen( $m[4] ) );
	$three = $m[5];
	return sprintf( '%s@%s.%s', $one, $two, $three );
}

/**
 * Obfuscates email addresses in a string.
 *
 * @param $string String possibly containing email address
 * @return string
 */
function mc4wp_obfuscate_email_addresses( $string ) {
	return preg_replace_callback( '/([\w\.]{1,4})([\w\.]*)\@(\w{1,2})(\w*)\.(\w+)/', '_mc4wp_obfuscate_email_addresses_callback', $string );
}

/**
 * Refreshes Mailchimp lists. This can take a while if the connected Mailchimp account has many lists.
 *
 * @return void
 */
function mc4wp_refresh_mailchimp_lists() {
	$mailchimp = new MC4WP_MailChimp();
	$mailchimp->refresh_lists();
}

/**
* Get element from array, allows for dot notation eg: "foo.bar"
*
* @param array $array
* @param string $key
* @param mixed $default
* @return mixed
*/
function mc4wp_array_get( $array, $key, $default = null ) {
	if ( is_null( $key ) ) {
		return $array;
	}

	if ( isset( $array[ $key ] ) ) {
		return $array[ $key ];
	}

	foreach ( explode( '.', $key ) as $segment ) {
		if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
			return $default;
		}

		$array = $array[ $segment ];
	}

	return $array;
}

/**
 * Filters string and strips out all HTML tags and attributes, except what's in our whitelist.
 *
 * @param string $string The string to apply KSES whitelist on
 * @return string
 * @since 4.8.8
 */
function mc4wp_kses( $string ) {
	$always_allowed_attr = array_fill_keys(
		array(
			'aria-describedby',
			'aria-details',
			'aria-label',
			'aria-labelledby',
			'aria-hidden',
			'class',
			'id',
			'style',
			'title',
			'role',
			'data-*',
			'tabindex',
		),
		true
	);
	$input_allowed_attr  = array_merge(
		$always_allowed_attr,
		array_fill_keys(
			array(
				'type',
				'required',
				'placeholder',
				'value',
				'name',
				'step',
				'min',
				'max',
				'checked',
				'width',
				'autocomplete',
				'autofocus',
				'minlength',
				'maxlength',
				'size',
				'pattern',
				'disabled',
				'readonly',
			),
			true
		)
	);

	$allowed         = array(
		'p'        => $always_allowed_attr,
		'label'    => array_merge( $always_allowed_attr, array( 'for' => true ) ),
		'input'    => $input_allowed_attr,
		'button'   => $input_allowed_attr,
		'fieldset' => $always_allowed_attr,
		'legend'   => $always_allowed_attr,
		'ul'       => $always_allowed_attr,
		'ol'       => $always_allowed_attr,
		'li'       => $always_allowed_attr,
		'select'   => array_merge( $input_allowed_attr, array( 'multiple' => true ) ),
		'option'   => array_merge( $input_allowed_attr, array( 'selected' => true ) ),
		'optgroup' => array(
			'disabled' => true,
			'label' => true,
		),
		'textarea' => array_merge(
			$input_allowed_attr,
			array(
				'rows' => true,
				'cols' => true,
			)
		),
		'div'      => $always_allowed_attr,
		'strong'   => $always_allowed_attr,
		'b'         => $always_allowed_attr,
		'i'         => $always_allowed_attr,
		'br'        => array(),
		'em'       => $always_allowed_attr,
		'span'     => $always_allowed_attr,
		'a'        => array_merge( $always_allowed_attr, array( 'href' => true ) ),
		'img'      => array_merge(
			$always_allowed_attr,
			array(
				'src' => true,
				'alt' => true,
				'width' => true,
				'height' => true,
				'srcset' => true,
				'sizes' => true,
				'referrerpolicy' => true,
			)
		),
		'u' => $always_allowed_attr,
	);

	return wp_kses( $string, $allowed );
}
