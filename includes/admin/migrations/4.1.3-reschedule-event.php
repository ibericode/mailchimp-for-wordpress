<?php

defined('ABSPATH') or exit;

wp_clear_scheduled_hook('mc4wp_refresh_mailchimp_lists');

$time_string = sprintf('tomorrow %d:%d%d am', rand(1, 6), rand(0, 5), rand(0, 9));
wp_schedule_event(strtotime($time_string), 'daily', 'mc4wp_refresh_mailchimp_lists');
