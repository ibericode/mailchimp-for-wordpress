<?php
/*
Plugin Name: PhpList for WordPress
Plugin URI: https://pl4wp.com/#utm_source=wp-plugin&utm_medium=phplist-for-wp&utm_campaign=plugins-page
Description: PhpList for WordPress by ibericode. Adds various highly effective sign-up methods to your site.
Version: 4.2.4
Author: ibericode
Author URI: https://ibericode.com/
Text Domain: phplist-for-wp
Domain Path: /languages
License: GPL v3

PhpList for WordPress
Copyright (C) 2012-2018, Danny van Kooten, hi@dannyvankooten.com

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
 * Bootstrap the PhpList for WordPress plugin
 *
 * @ignore
 * @access private
 * @return bool
 */
function _pl4wp_load_plugin() {

	global $pl4wp;

	// Don't run if PhpList for WP Pro 2.x is activated
	if( defined( 'PL4WP_VERSION' ) ) {
		return false;
	}

	// bootstrap the core plugin
	define( 'PL4WP_VERSION', '4.2.4' );
	define( 'PL4WP_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
	define( 'PL4WP_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );
	define( 'PL4WP_PLUGIN_FILE', __FILE__ );

	// load autoloader if function not yet exists (for compat with sitewide autoloader)
	if( ! function_exists( 'pl4wp' ) ) {
		require_once PL4WP_PLUGIN_DIR . 'vendor/autoload_52.php';
	}

	/**
	 * @global PL4WP_Container $GLOBALS['pl4wp']
	 * @name $pl4wp
	 */
	$pl4wp = pl4wp();
	$pl4wp['api'] = 'pl4wp_get_api_v3';
	$pl4wp['request'] = array( 'PL4WP_Request', 'create_from_globals' );
	$pl4wp['log'] = 'pl4wp_get_debug_log';

	// forms
	$pl4wp['forms'] = new PL4WP_Form_Manager();
	$pl4wp['forms']->add_hooks();

	// integration core
	$pl4wp['integrations'] = new PL4WP_Integration_Manager();
	$pl4wp['integrations']->add_hooks();

	// Doing cron? Load Usage Tracking class.
	if( isset( $_GET['doing_wp_cron'] ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		PL4WP_Usage_Tracking::instance()->add_hooks();
	}

	// Initialize admin section of plugin
	if( is_admin() ) {

		$admin_tools = new PL4WP_Admin_Tools();

		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$ajax = new PL4WP_Admin_Ajax( $admin_tools );
			$ajax->add_hooks();
		} else {
			$messages = new PL4WP_Admin_Messages();
			$pl4wp['admin.messages'] = $messages;

			$phplist = new PL4WP_PhpList();

			$admin = new PL4WP_Admin( $admin_tools, $messages, $phplist );
			$admin->add_hooks();

			$forms_admin = new PL4WP_Forms_Admin( $messages, $phplist );
			$forms_admin->add_hooks();

			$integrations_admin = new PL4WP_Integration_Admin( $pl4wp['integrations'], $messages, $phplist );
			$integrations_admin->add_hooks();
		}
	}

	return true;
}

// bootstrap custom integrations
function _pl4wp_bootstrap_integrations() {
	require_once PL4WP_PLUGIN_DIR . 'integrations/bootstrap.php';
}

add_action( 'plugins_loaded', '_pl4wp_load_plugin', 8 );
add_action( 'plugins_loaded', '_pl4wp_bootstrap_integrations', 90 );

/**
 * Flushes transient cache & schedules refresh hook.
 *
 * @ignore
 * @since 3.0
 */
function _pl4wp_on_plugin_activation() {
	$time_string = sprintf("tomorrow %d:%d%d am", rand(1,6), rand(0,5), rand(0, 9) );
	wp_schedule_event( strtotime( $time_string ), 'daily', 'pl4wp_refresh_phplist_lists' );
}

/**
 * Clears scheduled hook for refreshing PhpList lists.
 *
 * @ignore
 * @since 4.0.3
 */
function _pl4wp_on_plugin_deactivation() {
	global $wpdb;
	wp_clear_scheduled_hook( 'pl4wp_refresh_phplist_lists' );

	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pl4wp_phplist_list_%'");
}

register_activation_hook( __FILE__, '_pl4wp_on_plugin_activation' );
register_deactivation_hook( __FILE__, '_pl4wp_on_plugin_deactivation' );

