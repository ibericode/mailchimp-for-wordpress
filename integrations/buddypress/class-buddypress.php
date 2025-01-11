<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_BuddyPress_Integration
 *
 * @ignore
 */
class MC4WP_BuddyPress_Integration extends MC4WP_User_Integration
{
    /**
     * @var string
     */
    public $name = 'BuddyPress';

    /**
     * @var string
     */
    public $description = 'Subscribes users from BuddyPress registration forms.';


    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            add_action('bp_before_registration_submit_buttons', [ $this, 'output_checkbox' ], 20);
        }

        if (is_multisite()) {

            /**
             * Multisite signups are a two-stage process - the data is first added to
             * the 'signups' table and then converted into an actual user during the
             * activation process.
             *
             * To avoid all signups being subscribed to the Mailchimp list until they
             * have responded to the activation email, a value is stored in the signup
             * usermeta data which is retrieved on activation and acted upon.
             */
            add_filter('bp_signup_usermeta', [ $this, 'store_usermeta' ], 10, 1);
            add_action('bp_core_activated_user', [ $this, 'subscribe_from_usermeta' ], 10, 3);
        } else {
            add_action('bp_core_signup_user', [ $this, 'subscribe_from_form' ], 10, 4);
        }

        /**
         * There is one further issue to consider, which is that many BuddyPress
         * installs have a user moderation plugin (e.g. BP Registration Options)
         * installed. This is because email activation on itself is sometimes not enough to ensure
         * that user signups are not spammers. There should therefore be a way for
         * plugins to delay the Mailchimp signup process.
         *
         * Plugins can hook into the 'mc4wp_integration_buddypress_should_subscribe' filter to prevent
         * subscriptions from taking place:
         *
         * add_filter( 'mc4wp_integration_buddypress_should_subscribe', '__return_false' );
         *
         * The plugin would then then call:
         *
         * do_action( 'mc4wp_integration_buddypress_subscribe_user', $user_id );
         *
         * to perform the subscription at a later point.
         */
        add_action('mc4wp_integration_buddypress_subscribe_user', [ $this, 'subscribe_buddypress_user' ], 10, 1);
    }

    /**
     * Subscribes from BuddyPress Registration Form.
     *
     * @param int $user_id
     * @param string $user_login
     * @param string $user_password
     * @param string $user_email
     * @return bool
     */
    public function subscribe_from_form($user_id, $user_login, $user_password, $user_email)
    {
        if (! $this->triggered()) {
            return false;
        }

        $subscribe = true;

        /**
         * Allow other plugins to prevent the Mailchimp sign-up.
         *
         * @param bool $subscribe False does not subscribe the user.
         * @param int $user_id The user ID to subscribe
         */
        $subscribe = apply_filters('mc4wp_integration_buddypress_should_subscribe', $subscribe, $user_id);

        if (! $subscribe) {
            return false;
        }

        return $this->subscribe_buddypress_user($user_id);
    }

    /**
     * Stores subscription data from BuddyPress Registration Form.
     *
     * @param array $usermeta The existing usermeta
     * @return array $usermeta The modified usermeta
     */
    public function store_usermeta($usermeta)
    {

        // only add meta if triggered (checked)
        if ($this->triggered()) {
            $usermeta['mc4wp_subscribe'] = '1';
        }

        return $usermeta;
    }

    /**
     * Subscribes from BuddyPress Activation.
     *
     * @param int $user_id The activated user ID
     * @param string $key the activation key (not used)
     * @param array $userdata An array containing the activated user data
     * @return bool
     */
    public function subscribe_from_usermeta($user_id, $key, $userdata)
    {

        // sanity check
        if (empty($user_id)) {
            return false;
        }

        // bail if our usermeta key is not switched on
        $meta = ( isset($userdata['meta']) ) ? $userdata['meta'] : [];
        if (empty($meta['mc4wp_subscribe'])) {
            return false;
        }

        $subscribe = true;

        /**
         * @ignore Documented elsewhere, see MC4WP_BuddyPress_Integration::subscribe_from_form.
         */
        $subscribe = apply_filters('mc4wp_integration_buddypress_should_subscribe', $subscribe, $user_id);
        if (! $subscribe) {
            return false;
        }

        return $this->subscribe_buddypress_user($user_id);
    }

    /**
     * Subscribes a user to Mailchimp list(s).
     *
     * @param int $user_id The user ID to subscribe
     * @return bool
     */
    public function subscribe_buddypress_user($user_id)
    {
        $user = get_userdata($user_id);

        // was a user found with the given ID?
        if (! $user instanceof WP_User) {
            return false;
        }

        // gather email address and name from user
        $data = $this->user_merge_vars($user);

        return $this->subscribe($data, $user_id);
    }
    /* End BuddyPress functions */

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('BuddyPress');
    }
}
