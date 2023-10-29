<?php

/**
 * This will send the last coupon code used in the oder to MailChimp.
 *
 * @return array
 */
add_filter( 'mc4wp_integration_woocommerce_data', function( $data, $order_id ) {
	$order = wc_get_order( $order_id );
    if( $order->get_used_coupons() ) {
        foreach( $order->get_used_coupons() as $coupon) {
	        $custom_coupon_code_field = $coupon;
        }
        $data[ 'MMERGE5' ] = $custom_coupon_code_field;
    }
	return $data;
}, 10, 2);
