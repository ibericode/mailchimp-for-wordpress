<?php
/**
 * This will send additional WooCommerce checkout fields to MailChimp.
 *
 * @return array
 */
add_filter( 'mc4wp_integration_woocommerce_data', function( $data, $order_id ) {
	$order = wc_get_order( $order_id );

	// this sends the billing_country field from WooCommerce to a Mailchimp field called "BILLING_COUNTRY"
	$data[ 'BILLING_COUNTRY' ] = $order->get_billing_country();

	// if it's a custom checkout field, usually you can get its value like this:
	$data[ 'NAME_OF_FIELD_IN_MAILCHIMP' ] = $order->get_meta( 'name_of_field_in_woocommerce', true );
	
	// this sends the billing_phone field from WooCommerce to a Mailchimp field called "PHONE". 
	// I recommend using a text field, so that any format of phone number will pass validation in Mailchimp.
	$data[ 'PHONE' ] = $order->get_billing_phone();

	return $data;
}, 10, 2);
