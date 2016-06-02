<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_WooCommerce_Integration
 *
 * @ignore
 */
class MC4WP_WooCommerce_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	public $name = "WooCommerce Checkout";

	/**
	 * @var string
	 */
	public $description = "Subscribes your WooCommerce customers.";

	/**
	 * Add hooks
	 */
	public function add_hooks() {

		if( ! $this->options['implicit'] ) {
			// create hook name based on position setting
			$hook = sprintf( 'woocommerce_%s', $this->options['position'] );
			add_action( $hook, array( $this, 'output_checkbox' ), 20 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_woocommerce_checkout_checkbox_value' ) );
		}

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'subscribe_from_woocommerce_checkout' ) );
	}

	/**
	 * Add default value for "position" setting
	 *
	 * @return array
	 */
	protected function get_default_options() {
		$defaults = parent::get_default_options();
		$defaults['position'] = 'billing';
		return $defaults;
	}


	/**
	* @param int $order_id
	*/
	public function save_woocommerce_checkout_checkbox_value( $order_id ) {
		update_post_meta( $order_id, '_mc4wp_optin', $this->checkbox_was_checked() );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param $order_id
	 *
	 * @return bool|mixed
	 */
	public function triggered( $order_id = null ) {

		if( $this->options['implicit'] ) {
			return true;
		}

		if( ! $order_id ) {
			return false;
		}

		$do_optin = get_post_meta( $order_id, '_mc4wp_optin', true );
		return $do_optin;
	}

	/**
	* @param int $order_id
	* @return boolean
	*/
	public function subscribe_from_woocommerce_checkout( $order_id ) {

		if( ! $this->triggered( $order_id ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );

		$data = array(
			'EMAIL' => $order->billing_email,
			'NAME' => "{$order->billing_first_name} {$order->billing_last_name}",
			'FNAME' => $order->billing_first_name,
			'LNAME' => $order->billing_last_name,
		);

		// TODO: add billing address fields, maybe by finding MailChimp field of type "address"?

		return $this->subscribe( $data, $order_id );
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function get_object_link( $object_id ) {
		return sprintf( '<a href="%s">%s</a>', get_edit_post_link( $object_id ), sprintf( __( 'Order #%d', 'mailchimp-for-wp' ), $object_id ) );
	}

}