<?php

class MC4WP_Tools {

	/*
	 * Replacement output when performing string replacements
	 */
	public static $replacement_output = 'string';

	/**
	 * @param array $merge_vars
	 *
	 * @return mixed
	 */
	public static function guess_merge_vars( array $merge_vars ) {

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

		return $merge_vars;
	}

	/**
	 * Returns text with {variables} replaced.
	 *
	 * @param    string $string
	 * @param array     $additional_replacements
	 * @param array Array of list ID's (needed if {subscriber_count} is set
	 *
	 * @return string $text       The text with {variables} replaced.
	 * replaced.
	 */
	public static function replace_variables( $string, $additional_replacements = array(), $list_ids = array(), $output = 'string' ) {

		self::$replacement_output = $output;

		// replace general vars
		$replacements = array(
			'{ip}' => self::get_client_ip(),
			'{current_url}' => mc4wp_get_current_url(),
			'{current_path}' => ( ! empty( $_SERVER['REQUEST_URI'] ) ) ? esc_html( $_SERVER['REQUEST_URI'] ) : '',
			'{date}' => date( 'm/d/Y' ),
			'{time}' => date( 'H:i:s' ),
			'{language}' => defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_locale(),
			'{email}' => self::get_known_email(),
			'{user_email}' => '',
			'{user_firstname}' => '',
			'{user_lastname}' => '',
			'{user_name}' => '',
			'{user_id}' => '',
		);

		// setup replacements for logged-in users
		if ( is_user_logged_in()
		     && ( $user = wp_get_current_user() )
		     && ( $user instanceof WP_User ) ) {

			// logged in user, replace vars by user vars
			$replacements['{user_email}'] = $user->user_email;
			$replacements['{user_firstname}'] = $user->first_name;
			$replacements['{user_lastname}'] = $user->last_name;
			$replacements['{user_name}'] = $user->display_name;
			$replacements['{user_id}'] = $user->ID;
		}

		// merge with additional replacements
		$replacements = array_merge( $replacements, $additional_replacements );

		// subscriber count? only fetch these if the tag is actually used
		if ( stristr( $string, '{subscriber_count}' ) !== false ) {
			$mailchimp = new MC4WP_MailChimp();
			$subscriber_count = $mailchimp->get_subscriber_count( $list_ids );
			$replacements['{subscriber_count}'] = $subscriber_count;
		}

		// encode replacements when output type is set to 'url'
		if( self::$replacement_output === 'url' ) {
			$replacements = urlencode_deep( $replacements );
		}

		// perform the replacement
		$string = str_ireplace( array_keys( $replacements ), array_values( $replacements ), $string );

		// replace dynamic variables
		if( stristr( $string, '{data_' ) !== false ) {
			$string = preg_replace_callback('/\{data_([\w-.]+)( default=\"([^"]*)\"){0,1}\}/', array( 'MC4WP_Tools', 'replace_request_data_variables' ), $string );
		}

		return $string;
	}


	/**
	 * @param $matches
	 *
	 * @return string
	 */
	public static function replace_request_data_variables( $matches ) {

		$variable = strtoupper( $matches[1] );
		$default = ( ! empty( $matches[3] ) ) ? $matches[3] : '';

		$request_data = array_change_key_case( $_REQUEST, CASE_UPPER );

		if( isset( $request_data[ $variable ] ) && is_scalar( $request_data[ $variable ] ) ) {

			// return urlencoded variable if replacement output is set to 'url'
			if( self::$replacement_output === 'url' ) {
				return urlencode( $request_data[ $variable ] );
			}

			return esc_html( $request_data[ $variable ] );
		}

		return $default;
	}

	/**
	 * Returns the email address of the visitor if it is known to us
	 *
	 * @return string
	 */
	public static function get_known_email() {

		// case insensitive check in $_REQUEST
		$request_data = array_change_key_case( $_REQUEST, CASE_LOWER );

		if( isset( $request_data['email'] ) ) {
			$email = $request_data['email'];
		} elseif( isset( $request_data['mc4wp_email'] ) ) {
			$email = $request_data['mc4wp_email'];
		} elseif( isset( $_COOKIE['mc4wp_email'] ) ) {
			$email = $_COOKIE['mc4wp_email'];
		} else {
			$email = '';
		}

		return strip_tags( $email );
	}

	/**
	 * Returns the IP address of the visitor, does not take proxies into account.
	 *
	 * @return string
	 */
	public static function get_client_ip() {

		$headers = ( function_exists( 'apache_request_headers' ) ) ? apache_request_headers() : $_SERVER;

		if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$ip = $headers['X-Forwarded-For'];
		} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$ip = $headers['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
		}

		return $ip;
	}

	/**
	 * @param $email
	 */
	public static function remember_email( $email ) {

		/**
		 * @filter `mc4wp_cookie_expiration_time`
		 * @expects timestamp
		 * @default timestamp for 90 days from now
		 *
		 * Timestamp indicating when the email cookie expires, defaults to 90 days
		 */
		$expiration_time = apply_filters( 'mc4wp_cookie_expiration_time', strtotime( '+90 days' ) );

		setcookie( 'mc4wp_email', $email, $expiration_time, '/' );
	}

}