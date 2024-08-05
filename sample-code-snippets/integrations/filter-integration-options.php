<?php

/**
* This filter enables the "update existing subscribers" option for the MailChimp for WordPress Gravity Forms integration
*/
add_filter( 'mc4wp_integration_gravity-forms_options', function( $opts ) {
	$opts['update_existing'] = true;
	return $opts;
});
