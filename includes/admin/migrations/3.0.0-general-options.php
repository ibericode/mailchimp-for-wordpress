<?php

defined('ABSPATH') or exit;

// transfer option
$options = (array) get_option('mc4wp_lite', array());

// merge options, with Pro options taking precedence
$pro_options = (array) get_option('mc4wp', array());
$options     = array_merge($options, $pro_options);

// update options
update_option('mc4wp', $options);

// delete old option
delete_option('mc4wp_lite');
