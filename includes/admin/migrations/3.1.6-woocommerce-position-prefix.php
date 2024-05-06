<?php

defined('ABSPATH') or exit;

$options = get_option('mc4wp_integrations', array());

if (! empty($options['woocommerce']) && ! empty($options['woocommerce']['position'])) {
    $options['woocommerce']['position'] = sprintf('checkout_%s', $options['woocommerce']['position']);
}

update_option('mc4wp_integrations', $options);
