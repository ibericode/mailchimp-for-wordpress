<?php
/*
Plugin Name: MailChimp for WordPress Lite
Plugin URI: http://dannyvankooten.com/mailchimp-for-wordpress/
Description: Lite version of MailChimp for WordPress. Adds various sign-up methods to your website. 
Version: 1.5.5
Author: Danny van Kooten
Author URI: http://dannyvanKooten.com
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

if( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if(!function_exists('is_plugin_active')) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
}

// only load lite version if Pro is not active or being activated
if(!is_plugin_active('mailchimp-for-wp-pro/mailchimp-for-wp-pro.php') 
	&& !(is_admin() && isset($_GET['action']) && $_GET['action'] == 'activate' && isset($_GET['plugin']) && $_GET['plugin'] == 'mailchimp-for-wp-pro/mailchimp-for-wp-pro.php') ) {

	define("MC4WP_LITE_VERSION", "1.5.5");
	define("MC4WP_LITE_PLUGIN_DIR", plugin_dir_path(__FILE__));
	define("MC4WP_LITE_PLUGIN_URL", plugins_url( '/' , __FILE__ ) );

	require_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions.php';
	require_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-plugin.php';
	MC4WP_Lite::init();

	if(is_admin() && (!defined("DOING_AJAX") || !DOING_AJAX)) {
		
		// ADMIN
		require_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-admin.php';
		MC4WP_Lite_Admin::init();

	} 
	
} 
