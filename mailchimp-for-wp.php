<?php
/*
Plugin Name: MailChimp for WordPress Lite
Plugin URI: https://dannyvankooten.com/mailchimp-for-wordpress/
Description: Lite version of MailChimp for WordPress. Adds various sign-up methods to your website. 
Version: 2.1
Author: Danny van Kooten
Author URI: http://dannyvankooten.com
Text Domain: mailchimp-for-wp
Domain Path: /languages
License: GPL v3

MailChimp for WordPress
Copyright (C) 2012-2013, Danny van Kooten, hi@dannyvankooten.com

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
if( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* Loads the MailChimp for WP plugin files
*
* @return boolean True if the plugin files were loaded, false otherwise.
*/
function mc4wp_load_plugin() {

	// don't load plugin if user has the premium version installed and activated
	if( defined( 'MC4WP_VERSION' ) ) {
		return false;
	}

	// bootstrap the lite plugin
	define( 'MC4WP_LITE_VERSION', '2.1' );
	define( 'MC4WP_LITE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'MC4WP_LITE_PLUGIN_URL', plugins_url( '/' , __FILE__ ) );
	define( 'MC4WP_LITE_PLUGIN_FILE', __FILE__ );

	require_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions/general.php';
	require_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions/template.php';
	require_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-plugin.php';
	$GLOBALS['mc4wp'] = new MC4WP_Lite();

	if( is_admin() && ( false === defined( 'DOING_AJAX' ) || false === DOING_AJAX ) ) {
		
		// ADMIN
		require_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-admin.php';
		new MC4WP_Lite_Admin();

	} 

	return true;
}

add_action( 'plugins_loaded', 'mc4wp_load_plugin', 20 );
