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

		// set ip address
		if( empty( $merge_vars['OPTIN_IP'] ) ) {
			$merge_vars['OPTIN_IP'] = self::get_client_ip();
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

	/**
	 * @param $datetime
	 * @param string $format
	 *
	 * @return bool|string
	 */
	public static function mysql_datetime_to_local_datetime( $datetime, $format = '' ) {

		if( $format === '' ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		// add or subtract GMT offset to given mysql time
		$local_datetime = strtotime( $datetime ) + ( get_option( 'gmt_offset') * HOUR_IN_SECONDS );

		return date( $format, $local_datetime );
	}

	/**
	 * @return array
	 */
	public static function get_countries() {
		return array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curacao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'CD' => 'Democratic Republic of the Congo',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TL' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'CI' => 'Ivory Coast',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'XK' => 'Kosovo',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'CG' => 'Republic of the Congo',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SX' => 'Sint Maarten',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'VI' => 'U.S. Virgin Islands',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);
	}

}