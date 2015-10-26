<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'mc4wp_merge_vars', array( 'MC4WP_Tools', 'guess_merge_vars' ) );