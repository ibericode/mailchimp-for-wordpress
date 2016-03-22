<?php

/**
 * Try to include a file before each integration's settings page
 *
 * @param MC4WP_Integration $integration
 * @param array $opts
 * @ignore
 */
function mc4wp_admin_before_integration_settings( MC4WP_Integration $integration, $opts ) {
	$file = dirname( __FILE__ ) . sprintf( '/%s/admin-before.php', $integration->slug );

	if( file_exists( $file ) ) {
		include $file;
	}
}

/**
 * Try to include a file before each integration's settings page
 *
 * @param MC4WP_Integration $integration
 * @param array $opts
 * @ignore
 */
function mc4wp_admin_after_integration_settings( MC4WP_Integration $integration, $opts ) {
	$file = dirname( __FILE__ ) . sprintf( '/%s/admin-after.php', $integration->slug );

	if( file_exists( $file ) ) {
		include $file;
	}
}

add_action( 'mc4wp_admin_before_integration_settings', 'mc4wp_admin_before_integration_settings', 30, 2 );
add_action( 'mc4wp_admin_after_integration_settings', 'mc4wp_admin_after_integration_settings', 30, 2 );


// Register core integrations
mc4wp_register_integration( 'ninja-forms', 'MC4WP_Ninja_Forms_Integration' );
mc4wp_register_integration( 'wp-comment-form', 'MC4WP_Comment_Form_Integration' );
mc4wp_register_integration( 'wp-registration-form', 'MC4WP_Registration_Form_Integration' );
mc4wp_register_integration( 'buddypress', 'MC4WP_BuddyPress_Integration' );
mc4wp_register_integration( 'woocommerce', 'MC4WP_WooCommerce_Integration' );
mc4wp_register_integration( 'easy-digital-downloads', 'MC4WP_Easy_Digital_Downloads_Integration' );
mc4wp_register_integration( 'contact-form-7', 'MC4WP_Contact_Form_7_Integration', true );
mc4wp_register_integration( 'events-manager', 'MC4WP_Events_Manager_Integration' );
mc4wp_register_integration( 'custom', 'MC4WP_Custom_Integration', true );
