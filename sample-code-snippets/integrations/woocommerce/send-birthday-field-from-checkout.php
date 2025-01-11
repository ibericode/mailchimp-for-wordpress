<?php

/**
 * The following snippet assumes a checkout field with name "billing_birthdate" and sends the value of that field to a MailChimp list field named MMERGE3
 *
 * This assumes that the format of the billing_birthdate field is correct.
 */

add_filter('mc4wp_integration_woocommerce_data', function ($data, $order_id) {
    $order = wc_get_order($order_id);
    $data['MMERGE3'] = $order->get_meta('billing_birthdate', true);
    return $data;
}, 10, 2);
