<?php


/**
 * Returns a Form instance
 *
 * @api
 * @param    int     $form_id.
 * @return   MC4WP_Form
 */
function mc4wp_get_form( $form_id = 0 ) {
	return MC4WP_Form::get_instance( $form_id );
}

/**
 *
 * @api
 * @param       $form_id
 * @param array $attributes
 * @return string
 */
function mc4wp_show_form( $form_id, $attributes = array() ) {
	return MC4WP_Form_Manager::instance()->output_manager->output_form( $form_id, $attributes );
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

	try {
		$form = mc4wp_get_form( $form_id );
	} catch( Exception $e ) {
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

	try {
		$form = mc4wp_get_form( $form_id );
	} catch( Exception $e ) {
		return '';
	}

	// return empty string if this form is not submitted.
	if( ! $form->is_submitted() ) {
		return '';
	}

	return $form->request->get_response_html();
}