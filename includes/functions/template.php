<?php

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

