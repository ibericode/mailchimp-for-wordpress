<?php

// Explicitly declare incompatibility with WooCommerce Blocks
add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', MC4WP_PLUGIN_FILE, false);
    }
});


mc4wp_register_integration('woocommerce', 'MC4WP_WooCommerce_Integration');
