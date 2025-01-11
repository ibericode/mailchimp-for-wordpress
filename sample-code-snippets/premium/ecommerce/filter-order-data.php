<?php

add_filter('mc4wp_ecommerce_order_data', function ($data, $woocommerce_order) {

    // here, you can modify the $data array
    // see http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#create-post_ecommerce_stores_store_id_orders

    foreach ($data['lines'] as $index => $line) {
        // strip order lines with product 501
        if ($line['product_id'] == 501) {
            unset($data['lines'][$index]);
        }
    }

    // reset array because MailChimp API expects an array type.
    $data['lines'] = array_values($data['lines']);

    return $data;
}, 10, 2);
