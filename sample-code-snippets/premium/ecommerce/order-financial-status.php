<?php

// https://developer.mailchimp.com/documentation/mailchimp/guides/getting-started-with-ecommerce/#order-status-notifications
// financial_status:
//     - pending: sends the order confirmation
//     - paid: sends order invoice
//     - cancelled: sends cancellation confirmation
//     - refunded: sends refund confirmation
//
// fullfilment_status:
//      - shipped: sends shipping confirmation

add_filter('mc4wp_ecommerce_order_data', function ($data, $order) {
    switch ($order->get_status()) {
        case "pending":
            $data['financial_status'] =  'pending';
            break;

        case "on-hold":
            $data['financial_status'] =  'pending';
            break;

        case "processing":
            $data['financial_status'] =  'pending';
            break;

        case "completed":
            $data['financial_status'] =  'paid';
            $data['fulfillment_status'] = 'shipped';
            break;
    }

    return $data;
}, 10, 2);
