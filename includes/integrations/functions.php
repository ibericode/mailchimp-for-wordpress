<?php

/**
 * Gets an array of all registered integrations
 *
 * @since 3.0
 * @access public
 *
 * @return PL4WP_Integration[]
 */
function pl4wp_get_integrations() {
	return pl4wp('integrations')->get_all();
}

/**
 * Get an instance of a registered integration class
 *
 * @since 3.0
 * @access public
 *
 * @param string $slug
 *
 * @return PL4WP_Integration
 */
function pl4wp_get_integration( $slug ) {
	return pl4wp('integrations')->get( $slug );
}

/**
 * Register a new integration with PhpList for WordPress
 *
 * @since 3.0
 * @access public
 *
 * @param string $slug
 * @param string $class
 *
 * @param bool $always_enabled
 */
function pl4wp_register_integration( $slug, $class, $always_enabled = false ) {
	return pl4wp('integrations')->register_integration( $slug, $class, $always_enabled );
}

/**
 * Deregister a previously registered integration with PhpList for WordPress
 *
 * @since 3.0
 * @access public
 * @param string $slug
 */
function pl4wp_deregister_integration( $slug ) {
	pl4wp('integrations')->deregister_integration( $slug );
}