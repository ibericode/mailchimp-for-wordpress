<?php

defined( 'ABSPATH' ) or exit;

class MC4WP_WooCommerce_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	public $name = "WooCommerce Checkout";

	/**
	 * @var string
	 */
	public $description = "Adds a sign-up checkbox to your WooCommerce checkout form.";

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'woocommerce_checkout_billing', array( $this, 'output_checkbox' ), 20 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_woocommerce_checkout_checkbox_value' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'subscribe_from_woocommerce_checkout' ) );
	}


	/**
	* @param int $order_id
	*/
	public function save_woocommerce_checkout_checkbox_value( $order_id ) {
		update_post_meta( $order_id, '_mc4wp_optin', $this->checkbox_was_checked() );
	}

	/**
	* @param int $order_id
	* @return boolean
	*/
	public function subscribe_from_woocommerce_checkout( $order_id ) {

		$do_optin = get_post_meta( $order_id, '_mc4wp_optin', true );

		if( $do_optin ) {

			$order = new WC_Order( $order_id );
			$email = $order->billing_email;
			$merge_vars = array(
				'NAME' => "{$order->billing_first_name} {$order->billing_last_name}",
				'FNAME' => $order->billing_first_name,
				'LNAME' => $order->billing_last_name,
			);

			// @todo add billing address fields, find field of type "address"

			return $this->subscribe( $email, $merge_vars, $order_id );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return class_exists( 'WooCommerce' );
	}

}