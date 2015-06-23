<?php

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
* Gets the MailChimp for WP options from the database
* Uses default values to prevent undefined index notices.
*
* @param string $key
* @return array
*/
function mc4wp_get_options( $key = '' ) {

	$mc4wp = mc4wp();

	switch( $key ) {
		case 'general':
			return $mc4wp->options;
			break;

		case 'integrations':

		/** @deprecated 3.0 */
		case 'checkbox':
			return $mc4wp->integrations->options;
			break;

		case 'form':
			return $mc4wp->forms->options;
			break;
	}

	return null;
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

		if( is_numeric( $atts ) ) {
			$id = $atts;
			$atts = array(
				'id' => $id
			);
		}

		return mc4wp()->forms->output_form( $atts );
	}

}