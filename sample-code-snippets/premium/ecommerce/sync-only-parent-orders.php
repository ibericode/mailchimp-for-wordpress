<?php

// Some setups that work with Parent and Sub orders such as WCMp will result in both the parent and sub order being synced ot Mailchimp, resulting in every order being synced twice.
// This code snippet will make sure that only the parent order issynced and all sub orders are ignored during sync.

add_filter('mc4wp_ecommerce_send_order_to_mailchimp', function ($send, WC_Order $order) {
    if ($order->get_parent_id() > 0) {
        return false;
    }
    return $send;
}, 10, 2);
