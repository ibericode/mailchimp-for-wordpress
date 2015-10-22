<?php

/**
 * @todo Make this file PHP 5.2 compatible
 * @todo Register our own integrations here instead of hard-coding them in MC4WP_Integration_Manager class
 * @see MC4WP_Integration_Manager
 *
 * Preferably, we'd have something like mc4wp_register_integration( $slug, $class, $enabled )
 */

add_action( 'mc4wp_admin_before_integration_settings', function( MC4WP_Integration $integration ) {

	$file = dirname( __FILE__ ) . sprintf( '/%s/admin-before.php', $integration->slug );

	if( file_exists( $file ) ) {
		include $file;
	}

} );