<?php
/*
Plugin Name: MailChimp for WP Lite
Plugin URI: http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/
Description: Lite version of MailChimp for WordPress. Add various sign-up methods to your WordPress website. Show a sign-up form in your posts, pages or text widgets. Add a sign-up checkbox to various forms, like your comment form. <a href="http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/">Premium features include (multiple) AJAX powered forms, a form designer, an unlocked field wizard and much more.</a>
Version: 1.3.1
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

define("MC4WP_LITE_VERSION", "1.3.1");
define("MC4WP_LITE_PLUGIN_DIR", plugin_dir_path(__FILE__));

if(!function_exists('is_plugin_active')) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
}

// Only load Lite plugin is Pro version is not active
if(!is_plugin_active('mailchimp-for-wp-pro/mailchimp-for-wp-pro.php')) {
	include_once MC4WP_LITE_PLUGIN_DIR . 'includes/MC4WP_Lite.php';
	new MC4WP_Lite();
} 

