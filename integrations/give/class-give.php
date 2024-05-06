<?php

defined('ABSPATH') or exit;

/**
 * @ignore
 */
class MC4WP_Give_Integration extends MC4WP_Integration
{
    public $name        = 'Give';
    public $description = 'Subscribes people from your Give donation forms.';
    public $shown       = false;

    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            add_action('give_purchase_form_register_login_fields', array( $this, 'output_checkbox' ), 50);
        }

        add_action('give_checkout_before_gateway', array( $this, 'subscribe_from_give' ), 90, 2);
    }

    public function subscribe_from_give($posted, $user)
    {
        // was sign-up checkbox checked?
        if (true !== $this->triggered()) {
            return;
        }

        $merge_fields = array(
            'EMAIL' => $user['email'],
        );

        if (! empty($user['first_name'])) {
            $merge_fields['FNAME'] = $user['first_name'];
        }

        if (! empty($user['last_name'])) {
            $merge_fields['LNAME'] = $user['last_name'];
        }

        return $this->subscribe($merge_fields);
    }

    public function is_installed()
    {
        return defined('GIVE_VERSION');
    }
}
