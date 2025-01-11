<?php

/*
Plugin Name: MailChimp for WordPress - Debug Log
Plugin URI: https://mc4wp.com/kb/how-to-enable-log-debugging/
Description: Log all the things. This will log all sign-up attempts + any warnings & errors.
Author: ibericode
Version: 1.0
Author URI: https://ibericode.com/
*/

add_filter('mc4wp_debug_log_level', function () {
    return 'debug';
});
