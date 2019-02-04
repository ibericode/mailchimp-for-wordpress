<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Easy_Digital_Downloads_Integration
 *
 * @ignore
 */
class MC4WP_Easy_Digital_Downloads_Integration extends MC4WP_Integration
{

    /**
     * @var string
     */
    public $name = "Easy Digital Downloads";

    /**
     * @var string
     */
    public $description = "Subscribes your Easy Digital Downloads customers.";

    /**
     *
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {

            // TODO: Allow more positions
            add_action('edd_purchase_form_user_info_fields', array( $this, 'output_checkbox' ), 1);
            add_action('edd_payment_meta', array( $this, 'save_checkbox_value' ));
        }

        add_action('edd_complete_purchase', array( $this, 'subscribe_from_edd'), 50);
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function save_checkbox_value($meta)
    {

        // don't save anything if the checkbox was not checked
        if (! $this->checkbox_was_checked()) {
            return $meta;
        }

        $meta['_mc4wp_optin'] = 1;
        return $meta;
    }

    /**
     * {@inheritdoc}
     *
     * @param $object_id
     *
     * @return bool
     */
    public function triggered($object_id = null)
    {
        if ($this->options['implicit']) {
            return true;
        }

        if (! $object_id) {
            return false;
        }

        $meta = edd_get_payment_meta($object_id);
        if (is_array($meta) && isset($meta['_mc4wp_optin']) && $meta['_mc4wp_optin']) {
            return true;
        }

        return false;
    }

    /**
     * @param int $payment_id The ID of the payment
     *
     * @return bool|string
     */
    public function subscribe_from_edd($payment_id)
    {
        if (! $this->triggered($payment_id)) {
            return false;
        }

        $email = (string) edd_get_payment_user_email($payment_id);
        $data = array(
            'EMAIL' => $email
        );

        // add first and last name to merge vars, if given
        $user_info = (array) edd_get_payment_meta_user_info($payment_id);

        if (! empty($user_info['first_name']) && ! empty($user_info['last_name'])) {
            $data['NAME'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
        }

        if (! empty($user_info['first_name'])) {
            $data['FNAME'] = $user_info['first_name'];
        }

        if (! empty($user_info['last_name'])) {
            $data['LNAME'] = $user_info['last_name'];
        }

        return $this->subscribe($data, $payment_id);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('Easy_Digital_Downloads');
    }
}
