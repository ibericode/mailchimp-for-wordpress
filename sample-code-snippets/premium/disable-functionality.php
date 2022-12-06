<?php
/*
Plugin Name: MailChimp for WordPress - Disable Reports Log
Plugin URI: https://mc4wp.com/
Description: Disabled the Reports > Log section in MailChimp for WordPress Premium.
Author: ibericode
Version: 1.0
Author URI: https://ibericode.com/
*/


/**
 * This disables the "logging" functionality in the Premium add-on.
 */
add_filter( 'mc4wp_premium_enabled_plugins', function( $plugins ) {
	return array_diff( $plugins, array( 'logging' ) );
});