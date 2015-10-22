<?php


/**
 * @param string $slug
 *
 * @return array
 */
function mc4wp_get_integration_options( $slug = '' ) {

	$options = (array) get_option( 'mc4wp_integrations', array() );
	if( $slug === '' ) {
		return (array) apply_filters( 'mc4wp_integration_options', $options );
	}

	$integration_options = require MC4WP_PLUGIN_DIR . 'config/default-integration-options.php';
	if( isset( $options[ $slug ] ) && is_array( $options[ $slug] ) ) {
		$integration_options = array_merge( $integration_options, $options[ $slug ] );
	}

	$integration_options = (array) apply_filters( 'mc4wp_' . $slug . '_integration_options', $integration_options );

	return $integration_options;
}

/**
 * @param string $slug
 * @param string $class
 * @param bool $always_enabled
 */
function mc4wp_add_integration( $slug, $class, $always_enabled = false ) {
	MC4WP_Integration_Manager::instance()->add_integration( $slug, $class, $always_enabled );
}