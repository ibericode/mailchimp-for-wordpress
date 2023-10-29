<?php
/*
Plugin Name: MailChimp for WordPress - Selectively send e-commerce data
Plugin URI: https://mc4wp.com/
Description: Only send e-commerce data to Mailchimp if email address is already on the selected Mailchimp list
Author: ibericode
Version: 1.0
Author URI: https://ibericode.com/
*/

/**
 * Check if a list member has status pending or subscribed, using the list from the MC4WP > E-commerce page
 *
 * @see https://mailchimp.com/developer/reference/lists/list-members/
 * @param string $email
 * @return bool `true` when the mailchimp list member has the status pending or subscribed
 */
function mc4wp_mailchimp_list_member_has_status_pending_or_subscribed($email) {

	if(!is_string($email)) {
		throw new InvalidArgumentException(
			sprintf("mailchimp_list_member_has_status_pending_or_subscribed() expects parameter 1 to be string, %s given", gettype($email))
		);
	}

    $options = mc4wp('ecommerce.options');

    if (!isset($options['store']['list_id'])) {
        return false;
    }

    //Get the list id from mailchimp used in the E-commerce module
    $mailchimp_list_id = $options['store']['list_id'];

	try {
	    //Send the request to get the status
		$list_member = mc4wp_get_api_v3()->get_list_member($mailchimp_list_id, $email);
	} catch (Exception $e) {
	    //In case of an exception return false
		return false;
	}

    //Compare the status
	return in_array($list_member->status, array('subscribed', 'pending'), true);
}

/**
 * Extract the email from the stdClass, WP_User or WC_Customer.
 * The stdClass should contain the property `billing_email`.
 *
 * @param WP_User|WC_Customer|object $customer
 *
 * @return string the email address of the customer
 */
function mc4wp_get_customer_email($customer) {
    switch (get_class($customer)) {
        case stdClass::class:
            if (! isset($customer->billing_email)) {
                throw new InvalidArgumentException(
                    "stdClass does not contain a billing_email property"
                );
            }
            return $customer->billing_email;
            break;
        case WP_User::class:
            return $customer->user_email;
        case WC_Customer::class:
            return $customer->get_email();
    }

    throw new InvalidArgumentException(
        sprintf("get_customer_email() expects to be parameter 1 to be an instance of %s, %s or %s, given %s", stdClass::class, WP_User::class, WC_Customer::class, gettype($customer))
    );
}

/**
 * This filter is applied when a user is not logged in and abort on form fill on the checkout page
 *
 * @param bool $value the default value is `true`
 * @param WP_User|WC_Customer|object $customer
 * @return bool whenever the customer is send to mailchimp on `true` it will be send
 */
add_filter('mc4wp_ecommerce_send_cart_to_mailchimp', function ($value, $customer) {
    $customer_email = mc4wp_get_customer_email($customer);

	return mc4wp_mailchimp_list_member_has_status_pending_or_subscribed($customer_email);
}, 10, 2);

/**
 * This filter is applied when a client updates their profile.
 *
 * @param bool $value the default value is `true`
 * @param WP_User|WC_Customer|object $customer
 * @return bool whenever the customer is send to mailchimp on `true` it will be send
 */
add_filter('mc4wp_ecommerce_send_customer_to_mailchimp', function ($value, $customer) {
    $customer_email = mc4wp_get_customer_email($customer);

	return mc4wp_mailchimp_list_member_has_status_pending_or_subscribed($customer_email);
}, 10, 2);

/**
 * This filter is applied before a order is sent to Mailchimp
 *
 * @param bool $value the default value is `true`
 * @param WC_Order $order
 * @return bool whenever the customer is send to mailchimp on `true` it will be send
 */
add_filter('mc4wp_ecommerce_send_order_to_mailchimp', function ($value, WC_Order $order) {
    if (! $order->has_billing_address()) {
        return false;
    }

	return mc4wp_mailchimp_list_member_has_status_pending_or_subscribed($order->get_billing_email());
}, 10, 2);
