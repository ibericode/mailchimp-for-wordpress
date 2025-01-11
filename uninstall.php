<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

// Delete all MC4WP related options and transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'mc4wp' OR option_name LIKE 'mc4wp%' OR option_name LIKE '_transient_mc4wp_%' OR option_name LIKE '_transient_timeout_mc4wp_%';");

// Delete all MC4WP forms + settings
$wpdb->query("DELETE p, pm FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID WHERE p.post_type = 'mc4wp-form';");
