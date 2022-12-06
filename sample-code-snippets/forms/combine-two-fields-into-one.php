<?php

/**
 * Combine the values of "FIELD_ONE" and "FIELD_TWO" into a single "COMBINED_FIELD" field.
 *
 * @param array $data
 * @param MC4WP_Form $form
 *
 * @return array
 */
function myprefix_combine_fields( $data, MC4WP_Form $form ) {

	// get values for both fields
	$field1 = ( isset( $data['FIELD_ONE'] ) ) ? $data['FIELD_ONE'] : '';
	$field2 = ( isset( $data['FIELD_TWO'] ) ) ? $data['FIELD_TWO'] : '';

	// merge the two fields into one
    $data['COMBINED_FIELD'] = $field1 . ' ' . $field2;

	// return customized data
	return $data;
}

add_filter( 'mc4wp_form_data', 'myprefix_combine_fields', 10, 2 );