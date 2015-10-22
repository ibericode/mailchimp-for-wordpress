<?php

/**
 * @return array
 */
function mc4wp_get_integrations() {
	return MC4WP_Integration_Manager::instance()->integrations;
}

/**
 * Gets the (user-supplied) options for an integration
 *
 * @since 3.0
 * @api
 * @param string $slug
 * @return array
 */
function mc4wp_get_integration_options( $slug = '' ) {
	return MC4WP_Integration_Manager::instance()->get_options( $slug );
}

/**
 * Register a new integration with MailChimp for WordPress
 *
 * @since 3.0
 * @api
 * @param string $slug
 * @param string $class
 * @param bool $always_enabled
 */
function mc4wp_register_integration( $slug, $class, $always_enabled = false ) {
	MC4WP_Integration_Manager::instance()->register_integration( $slug, $class, $always_enabled );
}

/**
 * Deregister a previously registered integration with MailChimp for WordPress
 *
 * @since 3.0
 * @api
 * @param $slug
 */
function mc4wp_deregister_integration( $slug ) {
	MC4WP_Integration_Manager::instance()->deregister_integration( $slug );
}

/**
 * Get an instance of a registered integration class
 *
 * @since 3.0
 * @api
 * @param $slug
 * @return MC4WP_Integration
 */
function mc4wp_get_integration( $slug ) {
	return MC4WP_Integration_Manager::instance()->get_instance( $slug );
}