<?php

require __DIR__ . '/benchmark-mocks.php';

// make sure we're not running through all migrations
update_option('mc4wp_version', '999.1.1');

$memory = memory_get_usage();
$time_start = microtime(true);

require dirname(__DIR__, 1) . '/mailchimp-for-wp.php';

do_action('plugins_loaded');
do_action('setup_theme');
do_action('after_setup_theme');
do_action('init');
do_action('wp_loaded');

$time = round((microtime(true) - $time_start) * 1000, 2);
$memory_used = (memory_get_usage() - $memory) >> 10;

echo "Memory: $memory_used KB\n";
echo "Time: $time ms\n";
