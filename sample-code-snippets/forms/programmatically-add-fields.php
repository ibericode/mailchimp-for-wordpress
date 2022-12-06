<?php

/**
 * Programmatically adds another field to be sent to MailChimp.
 *
 * @param array      $data
 * @param MC4WP_Form $form
 *
 * @return array
 */
function myprefix_send_additional_field( array $data, MC4WP_Form $form ) {

	// a static field
    $data['MY_FIELD'] = 'Some value';

	// add the name of the form used
    $data['FORM'] = $form->name;

	return $data;
}

add_filter( 'mc4wp_form_data', 'myprefix_send_additional_field', 10, 2 );
