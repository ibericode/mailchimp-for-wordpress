<?php

/**
 * Try to include a file before each integration's settings page
 *
 * @param PL4WP_Integration $integration
 * @param array $opts
 * @ignore
 */
function pl4wp_admin_before_integration_settings( PL4WP_Integration $integration, $opts ) {

	$file = dirname( __FILE__ ) . sprintf( '/%s/admin-before.php', $integration->slug );

	if( file_exists( $file ) ) {
		include $file;
	}
}

/**
 * Try to include a file before each integration's settings page
 *
 * @param PL4WP_Integration $integration
 * @param array $opts
 * @ignore
 */
function pl4wp_admin_after_integration_settings( PL4WP_Integration $integration, $opts ) {
	$file = dirname( __FILE__ ) . sprintf( '/%s/admin-after.php', $integration->slug );

	if( file_exists( $file ) ) {
		include $file;
	}
}

add_action( 'pl4wp_admin_before_integration_settings', 'pl4wp_admin_before_integration_settings', 30, 2 );
add_action( 'pl4wp_admin_after_integration_settings', 'pl4wp_admin_after_integration_settings', 30, 2 );

// Register core integrations
pl4wp_register_integration( 'ninja-forms-2', 'PL4WP_Ninja_Forms_v2_Integration', true );
pl4wp_register_integration( 'wp-comment-form', 'PL4WP_Comment_Form_Integration' );
pl4wp_register_integration( 'wp-registration-form', 'PL4WP_Registration_Form_Integration' );
pl4wp_register_integration( 'buddypress', 'PL4WP_BuddyPress_Integration' );
pl4wp_register_integration( 'woocommerce', 'PL4WP_WooCommerce_Integration' );
pl4wp_register_integration( 'easy-digital-downloads', 'PL4WP_Easy_Digital_Downloads_Integration' );
pl4wp_register_integration( 'contact-form-7', 'PL4WP_Contact_Form_7_Integration', true );
pl4wp_register_integration( 'events-manager', 'PL4WP_Events_Manager_Integration' );
pl4wp_register_integration( 'memberpress', 'PL4WP_MemberPress_Integration' );
pl4wp_register_integration( 'affiliatewp', 'PL4WP_AffiliateWP_Integration' );

pl4wp_register_integration( 'custom', 'PL4WP_Custom_Integration', true );
$dir = dirname( __FILE__ );
require $dir . '/ninja-forms/bootstrap.php';
require $dir . '/wpforms/bootstrap.php';
require $dir . '/gravity-forms/bootstrap.php';

