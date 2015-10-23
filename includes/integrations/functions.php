<?php

/**
 * Gets an array of all registered integrations
 *
 * @since 3.0
 * @api
 * @return array
 */
function mc4wp_get_integrations() {
	return MC4WP_Integration_Manager::instance()->get_all();
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
	return MC4WP_Integration_Manager::instance()->get( $slug );
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
 * @return void
 */
function mc4wp_deregister_integration( $slug ) {
	MC4WP_Integration_Manager::instance()->deregister_integration( $slug );
}