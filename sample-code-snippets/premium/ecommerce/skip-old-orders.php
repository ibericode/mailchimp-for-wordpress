<?php

add_filter( 'mc4wp_ecommerce_send_order_to_mailchimp', function( $send, WC_Order $order ) {
    $time_one_month_ago = strtotime('-1 month' );
    $time_order_completed = strtotime( $order->get_date_completed() );
    $send = ( $time_order_completed > $time_one_month_ago );
    return $send;
}, 10, 2);
