<?php

if( ! function_exists( 'mc4wp_get_current_url' ) ) {

	/**
	 * Retrieves the URL of the current WordPress page
	 *
	 * @return  string  The current URL, escaped for safe usage inside attributes.
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

		// add trailing slash, if necessary
		if( substr( $_SERVER['REQUEST_URI'] , -1 ) === '/' ) {
			$url = trailingslashit( $url );
		}

		return esc_url( $url );
	}

}

if( ! function_exists( 'mc4wp_checkbox' ) ) {

	/**
	 * Echoes a sign-up checkbox.
	 */
	function mc4wp_checkbox() {
		$checkbox_manager = $GLOBALS['mc4wp']->get_checkbox_manager();

		if( ! isset( $checkbox_manager->integrations['comment_form'] ) ) {
			$checkbox_manager->integrations['comment_form'] = new MC4WP_Comment_Form_Integration();
		}

		$checkbox_manager->integrations['comment_form']->output_checkbox();
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
	 * @param array $atts
	 * @return string HTML of given form_id.
	 */
	function mc4wp_get_form( $atts = array() ) {

		$form_manager = $GLOBALS['mc4wp']->get_form_manager();

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
	 * @param   int     form ID
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

