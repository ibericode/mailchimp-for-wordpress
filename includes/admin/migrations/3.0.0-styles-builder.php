<?php

defined('ABSPATH') or exit;

// move stylebuilders file to bundle
$file = (string) get_option('mc4wp_custom_css_file', '');
if (empty($file)) {
    return;
}

$uploads = wp_upload_dir();

// figure out absolute file path
$prefix = str_replace('http:', '', $uploads['baseurl']);
$relative_path = str_replace($prefix, '', $file);

// get part before ?
if (strpos($relative_path, '?') !== false) {
    $parts = explode('?', $relative_path);
    $relative_path = array_shift($parts);
}

// This is the absolute path to the file, he he..
$file = $uploads['basedir'] . $relative_path;

if (file_exists($file)) {

    // create directory, if necessary
    $dir = $uploads['basedir'] . '/mc4wp-stylesheets';
    if (! file_exists($dir)) {
        @mkdir($dir, 0755);
    }

    @chmod($dir, 0755);

    // Move file to new location
    $new_file = $dir . '/bundle.css';
    $success = rename($file, $new_file);
}

// remove old option
delete_option('mc4wp_custom_css_file');
