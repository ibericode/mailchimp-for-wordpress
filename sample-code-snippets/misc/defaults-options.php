<?php

/**
 * This snippet will force-disable the "Load some CSS?" option for Checkboxes
 *
 * Valid option keys are:
 *
 * - precheck: Should the checkbox be pre-checked (boolean)
 * - css: Should the CSS stylesheet be loaded (boolean)
 * - label: The text for the checkbox label (string)
 */
add_filter( 'default_option_mc4wp_checkbox', function( $options ) {
	$options['css'] = 0; //Set value of "Load some default CSS?"" to false
	return $options;
});