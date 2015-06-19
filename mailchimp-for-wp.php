<?php
/*
Plugin Name: MailChimp for WordPress Lite
Plugin URI: https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-page
Description: Lite version of MailChimp for WordPress. Adds various sign-up methods to your website.
Version: 3.0
Author: ibericode
Author URI: http://ibericode.com/
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

	global $mc4wp;

	// don't load plugin if user has the premium version installed and activated
	if( defined( 'MC4WP_VERSION' ) ) {
		return false;
	}

	// load autoloader
	require_once dirname( __FILE__ ) . '/vendor/autoload_52.php';

	// define some constants
	require dirname( __FILE__ ) . '/bootstrap.php';

	// Initialize the plugin and store an instance in the global scope
	$mc4wp = mc4wp();
	$mc4wp->add_hooks();

	if( is_admin()
	    && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

		// load admin class
		$admin = new MC4WP_Admin( __FILE__ );
		$admin->add_hooks();
		$admin->load_translations();

		// load promotional elements
		$promotional_elements = new MC4WP_Promotional_Elements();
		$promotional_elements->add_hooks();
	}

	return true;
}

add_action( 'plugins_loaded', 'mc4wp_load_plugin', 20 );
