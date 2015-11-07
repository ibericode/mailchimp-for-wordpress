<?php

/**
 * Returns a Form instance
 *
 * @api
 * @param int $form_id.
 * @return MC4WP_Form
 */
function mc4wp_get_form( $form_id = 0 ) {
	return MC4WP_Form::get_instance( $form_id );
}

/**
 * Echoes the given form
 *
 * @api
 * @param int $form_id
 * @param array $config
 * @param bool $echo
 * @return string
 */
function mc4wp_show_form( $form_id, $config = array(), $echo = true ) {
	return mc4wp_get_instance('forms')->output_form( $form_id, $config, $echo );
}


/**
 * Check whether a form was submitted
 *
 * @api
 * @since 2.3.8
 * @param int $form_id The ID of the form you want to check. (optional)
 * @param string $element_id The ID of the form element you want to check, eg id="mc4wp-form-1" (optional)
 * @return boolean
 */
function mc4wp_form_is_submitted( $form_id = 0, $element_id = null ) {

	try {
		$form = mc4wp_get_form( $form_id );
	} catch( Exception $e ) {
		return false;
	}

	if( $element_id ) {
		$form_element = new MC4WP_Form_Element( $form, array( 'element_id' => $element_id ) );
		return $form_element->is_submitted;
	}

	return $form->is_submitted;
}

/**
 * @api
 * @since 2.3.8
 * @param int $form_id
 * @return string
 */
function mc4wp_form_get_response_html( $form_id = 0 ) {

	try {
		$form = mc4wp_get_form( $form_id );
	} catch( Exception $e ) {
		return '';
	}

	return $form->get_response_html();
}

/**
 * Gets an instance of the submitted form, if any.
 *
 * @return MC4WP_Form|null
 */
function mc4wp_get_submitted_form() {
	return mc4wp_get_instance('forms')->get_submitted_form();
}