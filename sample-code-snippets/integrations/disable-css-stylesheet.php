<?php

/**
 * This snippet will force-disable the "Load some CSS?" option for Checkboxes
 *
 * Valid options for the 'css' option are:
 *
 * - 1: Yes, load CSS file.
 * - 0: No, load no CSS file.
 */
add_filter( 'default_option_mc4wp_checkbox', function( $options ) {
	$options['css'] = 0; //Set value of "Load some default CSS?"" to false
	return $options;
});