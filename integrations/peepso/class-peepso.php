<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_PeepSo_Integration
 *
 * @ignore
 */
class MC4WP_PeepSo_Integration extends MC4WP_User_Integration
{
    /**
     * @var string
     */
    public $name = 'PeepSo';

    /**
     * @var string
     */
    public $description = 'Subscribes users from PeepSo registration forms.';

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            add_action('peepso_register_extended_fields', [$this, 'output_checkbox'], 20);
        }

        add_action('peepso_register_new_user', [$this, 'subscribe_from_peepso'], 10, 1);
    }

    /**
     * Subscribes from PeepSo Registration Form.
     *
     * @param int $user_id
     * @return bool
     */
    public function subscribe_from_peepso($user_id)
    {
        if (! $this->triggered()) {
            return false;
        }

        $user = get_userdata($user_id);

        // was a user found with the given ID?
        if (! $user instanceof WP_User) {
            return false;
        }

        $data = $this->user_merge_vars($user);

        return $this->subscribe($data, $user_id);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('PeepSo');
    }
}
