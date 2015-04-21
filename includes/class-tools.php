<?php

class MC4WP_Tools {

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
	 *
	 * @return string $text       The text with {variables} replaced.
	 * replaced.
	 */
	public static function replace_variables( $string, $additional_replacements = array() ) {

		// replace general vars
		$needles = array(
			'{ip}',
			'{current_url}',
			'{date}',
			'{time}',
			'{language}',
			'{email}',
			'{user_email}',
			'{user_firstname}',
			'{user_lastname}',
			'{user_name}',
			'{user_id}',
		);

		$replacements = array(
			$_SERVER['REMOTE_ADDR'],
			mc4wp_get_current_url(),
			date( 'm/d/Y' ),
			date( 'H:i:s' ),
			defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_locale(),
			self::get_known_email()
		);

		// setup replacements for logged-in users
		if ( is_user_logged_in()
		     && ( $user = wp_get_current_user() )
		     && ( $user instanceof WP_User ) ) {
			// logged in user, replace vars by user vars
			$user_replacements = array(
				$user->user_email,
				$user->first_name,
				$user->last_name,
				$user->display_name,
				$user->ID,
			);
		} else {
			$user_replacements = array_fill( 0, 5, '' );
		}

		// merge user replacements
		$replacements = array_merge( $replacements, $user_replacements );

		// merge both with additional replacements
		$needles = array_merge( $needles, array_keys( $additional_replacements ) );
		$replacements = array_merge( $replacements, array_values( $additional_replacements ) );

		// perform the replacement
		$string = str_ireplace( $needles, $replacements, $string );

		return $string;
	}

	/**
	 * @return string
	 */
	public static function get_known_email() {

		if( isset( $_GET['mc4wp_email'] ) ) {
			$email = $_GET['mc4wp_email'];
		} elseif( isset( $_COOKIE['mc4wp_email'] ) ) {
			$email = $_COOKIE['mc4wp_email'];
		} else {
			$email = '';
		}

		return $email;
	}



}