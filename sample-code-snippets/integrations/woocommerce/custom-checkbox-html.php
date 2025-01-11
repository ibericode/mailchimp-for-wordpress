<?php

// stop outputting default plugin checkbox
add_filter('mc4wp_integration_show_checkbox', function ($show, $integration_slug) {
    if ($integration_slug == 'woocommerce') {
        return false;
    }
    return $show;
}, 10, 2);


// output custom html checkbox in checkout form
add_action('woocommerce_review_order_before_submit', function () {
    // add HTML as you see fit here, but ensure the name and value of this element remains as it is
    echo '<input type="checkbox" name="_mc4wp_subscribe_woocommerce" value="1" />';
});
