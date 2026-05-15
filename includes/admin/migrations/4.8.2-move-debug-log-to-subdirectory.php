<?php

defined('ABSPATH') or exit;

// get old filename
$upload_dir   = wp_upload_dir(null, false);
$old_filename = trailingslashit($upload_dir['basedir']) . 'mc4wp-debug-log.php';

// if old file exists, move it to new location
if (is_file($old_filename)) {
    $new_filename = $upload_dir['basedir'] . '/mailchimp-for-wp/debug-log.php';
    $dir          = dirname($new_filename);
    if (! is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    rename($old_filename, $new_filename); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
}
