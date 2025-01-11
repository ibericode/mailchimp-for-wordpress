<?php

add_action('mc4wp_ecommerce_restore_abandoned_cart', function ($cart_data) {
    $wc_cart = WC()->cart;

    // empty cart
    $wc_cart->empty_cart();

    // add items from MailChimp cart object
    foreach ($cart_data->lines as $line) {
        $variation_id = $line->product_variant_id != $line->product_id ? $line->product_variant_id : 0;
        $wc_cart->add_to_cart($line->product_id, $line->quantity, $variation_id);
    }

    // redirect to cart page
    wp_redirect(wc_get_cart_url());
    exit;
});
