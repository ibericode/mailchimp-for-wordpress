<?php

/**
* Gets the MailChimp for WP options from the database
* Uses default values to prevent undefined index notices.
*
* @param string $key
* @return array
*/
function mc4wp_get_options( $key = '' ) {
	static $options = null;

	if( null === $options ) {

		$defaults = include MC4WP_PLUGIN_DIR . '/config/default-options.php';

		$db_keys_option_keys = array(
			'mc4wp' => 'general',
			'mc4wp_checkbox' => 'checkbox',
			'mc4wp_form' => 'form',
		);

		$options = array();
		foreach ( $db_keys_option_keys as $db_key => $option_key ) {
			$option = (array) get_option( $db_key, array() );

			// add option to database to prevent query on every pageload
			if ( count( $option ) === 0 ) {
				add_option( $db_key, $defaults[$option_key] );
			}

			$options[$option_key] = array_merge( $defaults[$option_key], $option );
		}
	}

	if( '' !== $key ) {
		return $options[$key];
	}

	return $options;
}

/**
 * @return MC4WP
 */
function mc4wp() {
	static $mc4wp;

	if( is_null( $mc4wp) ) {
		$mc4wp = new MC4WP();
	}

	return $mc4wp;
}

/**
* Gets the MailChimp for WP API class and injects it with the given API key
*
* @return MC4WP_API
*/
function mc4wp_get_api() {
	return mc4wp()->get_api();
}