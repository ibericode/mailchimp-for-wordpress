<?php

/**
 * Gets an array of all registered integrations
 *
 * @since 3.0
 * @access public
 *
 * @return MC4WP_Integration[]
 */
function mc4wp_get_integrations() {
	return mc4wp( 'integrations' )->get_all();
}

/**
 * Get an instance of a registered integration class
 *
 * @since 3.0
 * @access public
 *
 * @param string $slug
 *
 * @return MC4WP_Integration
 */
function mc4wp_get_integration( $slug ) {
	return mc4wp( 'integrations' )->get( $slug );
}

/**
 * Register a new integration with Mailchimp for WordPress
 *
 * @since 3.0
 * @access public
 *
 * @param string $slug
 * @param string $class
 *
 * @param bool $always_enabled
 */
function mc4wp_register_integration( $slug, $class, $always_enabled = false ) {
	return mc4wp( 'integrations' )->register_integration( $slug, $class, $always_enabled );
}

/**
 * Deregister a previously registered integration with Mailchimp for WordPress
 *
 * @since 3.0
 * @access public
 * @param string $slug
 */
function mc4wp_deregister_integration( $slug ) {
	mc4wp( 'integrations' )->deregister_integration( $slug );
}
