<?php

/*
Plugin Name: MC4WP: Mailchimp for WordPress
Plugin URI: https://www.mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-page
Description: Mailchimp for WordPress by ibericode. Adds various highly effective sign-up methods to your site.
Version: 4.9.18
Author: ibericode
Author URI: https://www.ibericode.com/
Text Domain: mailchimp-for-wp
Domain Path: /languages
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Mailchimp for WordPress
Copyright (C) 2012 - 2024, Danny van Kooten, hi@dannyvankooten.com

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
defined('ABSPATH') or exit;

/** @ignore */
function _mc4wp_load_plugin()
{
	global $mc4wp;

	// don't run if Mailchimp for WP Pro 2.x is activated
	if (defined('MC4WP_VERSION')) {
		return;
	}

	// don't run if PHP version is lower than 7.2.0
	if (PHP_VERSION_ID < 70200) {
		return;
	}

	// bootstrap the core plugin
	define('MC4WP_VERSION', '4.9.18');
	define('MC4WP_PLUGIN_DIR', __DIR__);
	define('MC4WP_PLUGIN_FILE', __FILE__);

	require __DIR__ . '/autoload.php';
	require __DIR__ . '/includes/default-actions.php';
	require __DIR__ . '/includes/default-filters.php';

	/**
	 * @global MC4WP_Container $GLOBALS['mc4wp']
	 * @name $mc4wp
	 */
	$mc4wp        = mc4wp();
	$mc4wp['api'] = 'mc4wp_get_api_v3';
	$mc4wp['log'] = 'mc4wp_get_debug_log';

	// forms
	$form_manager = new MC4WP_Form_Manager();
	$form_manager->add_hooks();
	$mc4wp['forms'] = $form_manager;

	// integration core
	$integration_manager = new MC4WP_Integration_Manager();
	$integration_manager->add_hooks();
	$mc4wp['integrations'] = $integration_manager;

	// Initialize admin section of plugin
	if (is_admin()) {
		$admin_tools = new MC4WP_Admin_Tools();

		if (defined('DOING_AJAX') && DOING_AJAX) {
			$ajax = new MC4WP_Admin_Ajax($admin_tools);
			$ajax->add_hooks();
		} else {
			$messages                = new MC4WP_Admin_Messages();
			$mc4wp['admin.messages'] = $messages;

			$admin = new MC4WP_Admin($admin_tools, $messages);
			$admin->add_hooks();

			$forms_admin = new MC4WP_Forms_Admin($messages);
			$forms_admin->add_hooks();

			$integrations_admin = new MC4WP_Integration_Admin($integration_manager, $messages);
			$integrations_admin->add_hooks();
		}
	}
}

function _mc4wp_on_plugin_activation()
{
	// schedule the action hook to refresh the stored Mailchimp lists on a daily basis
	$time_string = sprintf('tomorrow %d:%d am', rand(0, 7), rand(0, 59));
	wp_schedule_event(strtotime($time_string), 'daily', 'mc4wp_refresh_mailchimp_lists');
}

// bootstrap custom integrations
function _mc4wp_bootstrap_integrations()
{
	require_once MC4WP_PLUGIN_DIR . '/integrations/bootstrap.php';
}

add_action('plugins_loaded', '_mc4wp_load_plugin', 8);
add_action('plugins_loaded', '_mc4wp_bootstrap_integrations', 90);
register_activation_hook(__FILE__, '_mc4wp_on_plugin_activation');
