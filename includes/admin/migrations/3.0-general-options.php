<?php

defined( 'ABSPATH' ) or exit;

// transfer option
$options = get_option( 'mc4wp_lite' );
if( is_array( $options ) ) {
	update_option( 'mc4wp', $options );

	// delete old option
	delete_option( 'mc4wp_lite' );
}
