<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_AffiliateWP_Integration
 *
 * @ignore
 */
class MC4WP_AffiliateWP_Integration extends MC4WP_User_Integration
{
    /**
     * @var string
     */
    public $name = 'AffiliateWP';

    /**
     * @var string
     */
    public $description = 'Subscribes people from your AffiliateWP registration form.';

    /**
     * @var bool
     */
    public $shown = false;

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            add_action('affwp_register_fields_before_tos', [ $this, 'maybe_output_checkbox' ], 20);
        }

        add_action('affwp_register_user', [ $this, 'subscribe_from_registration' ], 90, 1);
    }

    /**
     * Output checkbox, once.
     */
    public function maybe_output_checkbox()
    {
        if (! $this->shown) {
            $this->output_checkbox();
            $this->shown = true;
        }
    }

    /**
     * Subscribes from WP Registration Form
     *
     * @param int $affiliate_id
     *
     * @return bool|string
     */
    public function subscribe_from_registration($affiliate_id)
    {

        // was sign-up checkbox checked?
        if (! $this->triggered()) {
            return false;
        }

        // gather emailadress from user who WordPress registered
        $user_id = affwp_get_affiliate_user_id($affiliate_id);
        $user    = get_userdata($user_id);

        // was a user found with the given ID?
        if (! $user instanceof WP_User) {
            return false;
        }

        $data = $this->user_merge_vars($user);

        return $this->subscribe($data, $user_id);
    }
    /* End registration form functions */


    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('Affiliate_WP');
    }
}
