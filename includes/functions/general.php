<?php

/**
* Gets the MailChimp for WP options from the database
* Uses default values to prevent undefined index notices.
*
* @return array
*/
function mc4wp_get_options() {
	$defaults = require MC4WP_PLUGIN_DIR . 'config/default-settings.php';
	$options = (array) get_option( 'mc4wp', array() );
	return array_merge( $defaults, $options );
}

/**
 * @param string $slug
 *
 * @return array
 */
function mc4wp_get_integration_options( $slug ) {
	$defaults = require MC4WP_PLUGIN_DIR . 'config/default-integration-options.php';
	$options = (array) get_option( 'mc4wp_integrations', array() );

	if( isset( $options[ $slug ] ) && is_array( $options[ $slug] ) ) {
		return array_merge( $defaults, $options[ $slug ] );
	}

	return $defaults;
}

/**
 * @return MC4WP
 */
function mc4wp() {
	return MC4WP::instance();
}

/**
* Gets the MailChimp for WP API class and injects it with the given API key
* @since 1.0
* @return MC4WP_API
*/
function mc4wp_get_api() {
	return MC4WP::instance()->get_api();
}

/**
 * Check whether a form was submitted
 *
 * @since 2.3.8
 * @param int $form_id The ID of the form you want to check. (optional)
 * @param string $element_id The ID of the form element you want to check, eg id="mc4wp-form-1" (optional)
 * @return boolean
 */
function mc4wp_form_is_submitted( $form_id = 0, $element_id = null ) {
	$form = mc4wp_get_form( $form_id );

	if( ! $form instanceof MC4WP_Form ) {
		return false;
	}

	return $form->is_submitted( $element_id );
}

/**
 * @since 2.3.8
 * @param int $form_id
 * @return string
 */
function mc4wp_form_get_response_html( $form_id = 0 ) {
	$form = mc4wp_get_form( $form_id );

	// return empty string if form isn't submitted.
	if( ! $form instanceof MC4WP_Form || ! $form->is_submitted() ) {
		return '';
	}

	return $form->request->get_response_html();
}