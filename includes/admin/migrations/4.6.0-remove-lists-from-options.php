<?php

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'mc4wp_mailchimp_list_%'" );
