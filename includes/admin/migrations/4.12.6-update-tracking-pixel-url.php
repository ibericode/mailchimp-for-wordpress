<?php

defined('ABSPATH') or exit;

$opts = get_option('mc4wp', []);

if (! empty($opts['tracking_pixel_id'])) {
    if (empty($opts['tracking_pixel_script_url'])) {
        $opts['tracking_pixel_script_url'] = sprintf('https://chimpstatic.com/mcjs-connected/js/users/%s.js', $opts['tracking_pixel_id']);
    }

    if (empty($opts['tracking_pixel_site_id'])) {
        $opts['tracking_pixel_site_id'] = $opts['tracking_pixel_id'];
    }

    unset($opts['tracking_pixel_id']);
    update_option('mc4wp', $opts);
}
