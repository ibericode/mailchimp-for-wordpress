<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_WooCommerce_Integration
 *
 * @ignore
 */
class MC4WP_WooCommerce_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'WooCommerce Checkout';

    /**
     * @var string
     */
    public $description = "Subscribes people from WooCommerce's checkout form.";

    /**
     * @var string[]
     */
    public $checkbox_classes = array(
        'input-checkbox',
    );

    public $wrapper_classes = array(
        'form-row',
        'form-row-wide',
    );

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (!$this->options['implicit']) {
            if ($this->options['position'] !== 'after_email_field') {
                // create hook name based on position setting
                $hook = $this->options['position'];

                // prefix hook with woocommerce_ if not already properly prefixed
                // note: we check for cfw_ prefix here to not override the Checkout for WC hook names
                if (strpos($hook, 'cfw_') !== 0 && strpos($hook, 'woocommerce_') !== 0) {
                    $hook = sprintf('woocommerce_%s', $hook);
                }

                add_action($hook, array($this, 'output_checkbox'), 20);
            } else {
                add_filter('woocommerce_form_field_email', array($this, 'add_checkbox_after_email_field'), 10, 4);
            }

            add_action('woocommerce_checkout_update_order_meta', array($this, 'save_woocommerce_checkout_checkbox_value'));

            // specific hooks for klarna
            add_filter('kco_create_order', array($this, 'add_klarna_field'));
            add_filter('klarna_after_kco_confirmation', array($this, 'subscribe_from_klarna_checkout'), 10, 2);

            // hooks for when using WooCommerce Blocks
            // TODO: Wait for this to stabilize in WooCommerce core and then work from the below
            // add_action('woocommerce_init',
            //  function () {
            //      $params = array(
            //          'id'       => 'marketing/'.$this->checkbox_name,
            //          'label'    => $this->get_label_text(),
            //          'location' => 'contact',
            //          'type'     => 'checkbox',
            //          'required' => false,
            //      );
            //      // Function was marked experimental from between WooCommerce v8.3 - v8.8
            //      if (function_exists('__experimental_woocommerce_blocks_register_checkout_field')) {
            //          __experimental_woocommerce_blocks_register_checkout_field($params);
            //      }
            //      if (function_exists('woocommerce_blocks_register_checkout_field')) {
            //          woocommerce_blocks_register_checkout_field($params);
            //      }
            //  }
            // );
            // add_action('woocommerce_set_additional_field_value', array($this, 'woocommerce_set_additional_field_value'), 10, 4);
        }

        add_action('woocommerce_checkout_order_processed', array($this, 'subscribe_from_woocommerce_checkout'));
    }

    // public function woocommerce_set_additional_field_value( $key, $value, $group, $wc_object ) {
    //  if ( 'marketing/'.$this->checkbox_name !== $key ) {
    //      return;
    //  }

    //  if ($value) {
    //      $wc_object->update_meta_data( '_mc4wp_optin', $value, true );
    //  }

    // }

    /**
     * Add default value for "position" setting
     *
     * @return array
     */
    protected function get_default_options()
    {
        $defaults             = parent::get_default_options();
        $defaults['position'] = 'billing';
        return $defaults;
    }

    public function add_klarna_field($create)
    {
        $create['options']['additional_checkbox']['text']     = $this->get_label_text();
        $create['options']['additional_checkbox']['checked']  = (bool) $this->options['precheck'];
        $create['options']['additional_checkbox']['required'] = false;
        return $create;
    }

    public function add_checkbox_after_email_field($field, $key, $args, $value)
    {
        if ($key !== 'billing_email') {
            return $field;
        }

        return $field . PHP_EOL . $this->get_checkbox_html();
    }

    /**
     * @param int $order_id
     */
    public function save_woocommerce_checkout_checkbox_value($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $order->update_meta_data('_mc4wp_optin', $this->checkbox_was_checked());
        $order->save();
    }

    /**
     * {@inheritdoc}
     *
     * @param $order_id
     *
     * @return bool|mixed
     */
    public function triggered($order_id = null)
    {
        if ($this->options['implicit']) {
            return true;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        return $order->get_meta('_mc4wp_optin');
    }

    public function subscribe_from_klarna_checkout($order_id, $klarna_order)
    {
        // $klarna_order is the returned object from Klarna
        if (false === (bool) $klarna_order['merchant_requested']['additional_checkbox']) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // store _mc4wp_optin in order meta
        $order->update_meta_data('_mc4wp_optin', true);
        $order->save();

        // continue in regular subscribe flow
        $this->subscribe_from_woocommerce_checkout($order_id);
        return;
    }

    /**
     * @param int $order_id
     * @return boolean
     */
    public function subscribe_from_woocommerce_checkout($order_id)
    {
        if (!$this->triggered($order_id)) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        if (method_exists($order, 'get_billing_email')) {
            $data = array(
                'EMAIL' => $order->get_billing_email(),
                'NAME'  => "{$order->get_billing_first_name()} {$order->get_billing_last_name()}",
                'FNAME' => $order->get_billing_first_name(),
                'LNAME' => $order->get_billing_last_name(),
            );
        } else {
            // NOTE: for compatibility with WooCommerce < 3.0
            $data = array(
                'EMAIL' => $order->billing_email,
                'NAME'  => "{$order->billing_first_name} {$order->billing_last_name}",
                'FNAME' => $order->billing_first_name,
                'LNAME' => $order->billing_last_name,
            );
        }

        // TODO: add billing address fields, maybe by finding Mailchimp field of type "address"?

        return $this->subscribe($data, $order_id);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('WooCommerce');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get_object_link($object_id)
    {
        return sprintf('<a href="%s">%s</a>', get_edit_post_link($object_id), sprintf(__('Order #%d', 'mailchimp-for-wp'), $object_id));
    }
}
