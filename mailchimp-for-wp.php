<?php
/*
Plugin Name: MailChimp for WordPress
Plugin URI: https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-page
Description: MailChimp for WordPress by ibericode. Adds various highly effective sign-up methods to your site.
Version: 3.0.9
Author: ibericode
Author URI: https://ibericode.com/
Text Domain: mailchimp-for-wp
Domain Path: /languages
License: GPL v3

MailChimp for WordPress
Copyright (C) 2012-2015, Danny van Kooten, hi@dannyvankooten.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Prevent direct file access
defined( 'ABSPATH' ) or exit;

/**
 * Bootstrap the MailChimp for WordPress plugin
 *
 * @ignore
 * @access private
 * @return bool
 */
function __mc4wp_load_plugin() {

	global $mc4wp;

	// Don't run if MailChimp for WP Pro 2.x is activated
	if( defined( 'MC4WP_VERSION' ) ) {
		return false;
	}

	// bootstrap the core plugin
	define( 'MC4WP_VERSION', '3.0.9' );
	define( 'MC4WP_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
	define( 'MC4WP_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );
	define( 'MC4WP_PLUGIN_FILE', __FILE__ );

	// load autoloader
	require_once MC4WP_PLUGIN_DIR . 'vendor/autoload_52.php';

	/**
	 * @global MC4WP_Container $GLOBALS['mc4wp']
	 * @name $mc4wp
	 */
	$mc4wp = mc4wp();
	$mc4wp['api'] = 'mc4wp_get_api';
	$mc4wp['request'] = array( 'MC4WP_Request', 'create_from_globals' );

	// forms
	$mc4wp['forms'] = new MC4WP_Form_Manager();
	$mc4wp['forms']->add_hooks();

	// integration core
	$mc4wp['integrations'] = new MC4WP_Integration_Manager();
	$mc4wp['integrations']->add_hooks();

	// visitor tracking
	$mc4wp['tracking'] = new MC4WP_Visitor_Tracking();
	$mc4wp['tracking']->add_hooks();

	// bootstrap custom integrations
	require_once MC4WP_PLUGIN_DIR . 'integrations/bootstrap.php';

	// Doing cron? Load Usage Tracking class.
	if( defined( 'DOING_CRON' ) && DOING_CRON ) {
		MC4WP_Usage_Tracking::instance()->add_hooks();
	}

	// Initialize admin section of plugin
	if( is_admin()
	    && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

		$messages = new MC4WP_Admin_Messages();
		$mailchimp = new MC4WP_MailChimp();

		$admin = new MC4WP_Admin( $messages, $mailchimp );
		$admin->add_hooks();

		$forms_admin = new MC4WP_Forms_Admin( $messages, $mailchimp );
		$forms_admin->add_hooks();

		$integrations_admin = new MC4WP_Integration_Admin( $mc4wp['integrations'], $messages, $mailchimp );
		$integrations_admin->add_hooks();
	}

	return true;
}

add_action( 'plugins_loaded', '__mc4wp_load_plugin', 20 );

/**
 * Flushes all MailChimp caches
 *
 * @ignore
 * @access private
 * @since 3.0
 */
function __mc4wp_flush() {
	delete_transient( 'mc4wp_mailchimp_lists' );
	delete_transient( 'mc4wp_mailchimp_lists_fallback' );
}

register_activation_hook( __FILE__, '__mc4wp_flush' );
