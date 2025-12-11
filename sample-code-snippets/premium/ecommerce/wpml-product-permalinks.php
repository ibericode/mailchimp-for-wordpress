<?php

/**
 * Plugin Name: Fix WPML Product Permalink in Mailchimp products
 *
 * This snippet sets the product permalink to its translated version when using WooCommerce + WPML
 *
 * See https://github.com/ibericode/mailchimp-for-wordpress/issues/775
 */

add_filter('mc4wp_ecommerce_product_data', function ($data, WC_Product $product) {
    $language = apply_filters('wpml_element_language_code', null, array(
        'element_id' => $data['id'],
        'element_type' => 'product'
    ));
    $data['url'] = apply_filters('wpml_permalink', $data['url'], $language);
    return $data;
}, 10, 2);

add_filter('mc4wp_ecommerce_product_variants_data', function ($variants, $product) {
    foreach ($variants as $key => $variant) {
        $language = apply_filters('wpml_element_language_code', null, array(
            'element_id' => $variant['id'],
            'element_type' => 'product'
        ));
        $variants[$key]['url'] = apply_filters('wpml_permalink', $variant['url'], $language);
    }
    return $variants;
}, 10, 2);
