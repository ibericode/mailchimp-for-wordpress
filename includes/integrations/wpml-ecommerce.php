<?php

/**
 * WPML compatibility for Mailchimp ecommerce product permalinks.
 *
 * When WooCommerce products are synced to Mailchimp via the ecommerce module,
 * the product URLs may not include the correct WPML language context, causing
 * translated products to link to the default language version.
 *
 * These functions hook into the ecommerce product data filters to ensure
 * product URLs are generated with the correct WPML language.
 *
 * @since 4.11.2
 * @see https://github.com/ibericode/mailchimp-for-wordpress/issues/775
 */

defined('ABSPATH') or exit;

/**
 * Fixes the product permalink for WPML translated products.
 *
 * Hooks into the ecommerce product data filter to replace the product URL
 * with the correct WPML-translated permalink.
 *
 * @since 4.11.2
 *
 * @param array $data Product data array containing 'id' and 'url' keys.
 * @return array Modified product data with corrected URL.
 */
function mc4wp_wpml_ecommerce_product_permalink($data)
{
    if (!defined('ICL_SITEPRESS_VERSION')) {
        return $data;
    }

    $language = apply_filters('wpml_element_language_code', null, [
        'element_id'   => $data['id'],
        'element_type' => 'product',
    ]);

    $data['url'] = apply_filters('wpml_permalink', $data['url'], $language);

    return $data;
}

/**
 * Fixes the product variant permalinks for WPML translated products.
 *
 * Hooks into the ecommerce product variants data filter to replace each
 * variant URL with the correct WPML-translated permalink.
 *
 * @since 4.11.2
 *
 * @param array $variants Array of variant data arrays, each containing 'id' and 'url' keys.
 * @return array Modified variants data with corrected URLs.
 */
function mc4wp_wpml_ecommerce_product_variants_permalink($variants)
{
    if (!defined('ICL_SITEPRESS_VERSION')) {
        return $variants;
    }

    foreach ($variants as $key => $variant) {
        $language = apply_filters('wpml_element_language_code', null, [
            'element_id'   => $variant['id'],
            'element_type' => 'product',
        ]);

        $variants[$key]['url'] = apply_filters('wpml_permalink', $variant['url'], $language);
    }

    return $variants;
}
