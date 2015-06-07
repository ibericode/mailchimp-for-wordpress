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

if( ! function_exists( 'mc4wp_get_current_url' ) ) {

	/**
	 * Retrieves the URL of the current WordPress page
	 *
	 * @return  string  The current URL, escaped for safe usage inside attributes.
	 */
	function mc4wp_get_current_url() {
		return MC4WP_Tools::get_current_url();
	}

}

if( ! function_exists( 'mc4wp_checkbox' ) ) {

	/**
	 * Echoes a sign-up checkbox.
	 */
	function mc4wp_checkbox() {
		mc4wp()->integrations->comment_form->output_checkbox();
	}

}

if( ! function_exists( 'mc4wp_form' ) ) {

	/**
	 * Echoes sign-up form with given $form_id.
	 *
	 * @param array $atts
	 */
	function mc4wp_form( $atts = array() ) {
		echo mc4wp_get_form( $atts );
	}

}

if( ! function_exists( 'mc4wp_get_form' ) ) {

	/**
	 * Returns HTML for sign-up form with the given $form_id.
	 *
	 * @param array|int $atts
	 * @return string HTML of given form_id.
	 */
	function mc4wp_get_form( $atts = array() ) {

		/** @var MC4WP_Form_Manager $form_manager */
		$form_manager = mc4wp()->form_manager;

		if( is_numeric( $atts ) ) {
			$id = $atts;
			$atts = array(
				'id' => $id
			);
		}

		return $form_manager->output_form( $atts );
	}

}


/****************************~***
 *      Deprecated functions    *
 ********************************/

if( ! function_exists( 'mc4wp_show_form' ) ) {
	/**
	 * Echoes a sign-up form.
	 *
	 * @param int $form_id form ID
	 *
	 * @deprecated 1.3.1 Use mc4wp_form() instead.
	 * @see        mc4wp_form()
	 */
	function mc4wp_show_form( $form_id ) {
		_deprecated_function( __FUNCTION__, 'MailChimp for WordPress v1.3.1', 'mc4wp_form' );
		echo mc4wp_get_form( $form_id );
	}

}

if( ! function_exists( 'mc4wp_show_checkbox' ) ) {
	/**
	 * Echoes a sign-up checkbox.
	 *
	 * @deprecated 1.3.1 Use mc4wp_checkbox() instead
	 * @see        mc4wp_checkbox()
	 */
	function mc4wp_show_checkbox() {
		_deprecated_function( __FUNCTION__, 'MailChimp for WordPress v1.3.1', 'mc4wp_checkbox' );
		mc4wp_checkbox();
	}
}