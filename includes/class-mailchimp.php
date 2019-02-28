<?php

/**
* @access private
* @ignore
*/
class MC4WP_MailChimp
{

    /**
    * @var string
    */
    public $error_code = '';

    /**
    * @var string
    */
    public $error_message = '';

    /**
    *
    * Sends a subscription request to the Mailchimp API
    *
    * @param string  $list_id           The list id to subscribe to
    * @param string  $email_address             The email address to subscribe
    * @param array    $args
    * @param boolean $update_existing   Update information if this email is already on list?
    * @param boolean $replace_interests Replace interest groupings, only if update_existing is true.
    *
    * @return object
    */
    public function list_subscribe($list_id, $email_address, array $args = array(), $update_existing = false, $replace_interests = true)
    {
        $this->reset_error();

        $default_args = array(
            'status' => 'pending',
            'email_address' => $email_address,
            'interests' => array(),
            'merge_fields' => array(),
        );
        $already_on_list = false;

        // setup default args
        $args = $args + $default_args;

        // first, check if subscriber is already on the given list
        try {
            $existing_member_data = $this->get_api()->get_list_member($list_id, $email_address);

            if ($existing_member_data->status === 'subscribed') {
                $already_on_list = true;

                // if we're not supposed to update, bail.
                if (! $update_existing) {
                    $this->error_code = 214;
                    $this->error_message = 'That subscriber already exists.';
                    return null;
                }

                $args['status'] = 'subscribed';

                // this key only exists if list actually has interests
                if (isset($existing_member_data->interests)) {
                    $existing_interests = (array) $existing_member_data->interests;

                    // if replace, assume all existing interests disabled
                    if ($replace_interests) {
                        $existing_interests = array_fill_keys(array_keys($existing_interests), false);
                    }

                    // TODO: Use array_replace here (PHP 5.3+)
                    $new_interests = $args['interests'];
                    $args['interests'] = $existing_interests;
                    foreach ($new_interests as $interest_id => $interest_status) {
                        $args['interests']["{$interest_id}"] = $interest_status;
                    }
                }
            } elseif ($args['status']  === 'pending' && $existing_member_data->status === 'pending') {
                // if status is "pending", delete & then re-subscribe
                // this ensures that a new double opt-in email is send out
                $this->get_api()->delete_list_member($list_id, $email_address);
            }
        } catch (MC4WP_API_Resource_Not_Found_Exception $e) {
            // subscriber does not exist (not an issue in this case)
        } catch (MC4WP_API_Exception $e) {
            // other errors.
            $this->error_code = $e->getCode();
            $this->error_message = $e;
            return null;
        }

        try {
            $data = $this->get_api()->add_list_member($list_id, $args);
        } catch (MC4WP_API_Exception $e) {
            $this->error_code = $e->getCode();
            $this->error_message = $e;
            return null;
        }

        $data->was_already_on_list = $already_on_list;

        return $data;
    }

    /**
    * Changes the subscriber status to "unsubscribed"
    *
    * @param string $list_id
    * @param string $email_address
    *
    * @return boolean
    */
    public function list_unsubscribe($list_id, $email_address)
    {
        $this->reset_error();

        try {
            $this->get_api()->update_list_member($list_id, $email_address, array( 'status' => 'unsubscribed' ));
        } catch (MC4WP_API_Resource_Not_Found_Exception $e) {
            // if email wasn't even on the list: great.
            return true;
        } catch (MC4WP_API_Exception $e) {
            $this->error_code = $e->getCode();
            $this->error_message = $e;
            return false;
        }

        return true;
    }

    /**
    * Deletes the subscriber from the given list.
    *
    * @param string $list_id
    * @param string $email_address
    *
    * @return boolean
    */
    public function list_unsubscribe_delete($list_id, $email_address)
    {
        $this->reset_error();

        try {
            $this->get_api()->delete_list_member($list_id, $email_address);
        } catch (MC4WP_API_Resource_Not_Found_Exception $e) {
            // if email wasn't even on the list: great.
            return true;
        } catch (MC4WP_API_Exception $e) {
            $this->error_code = $e->getCode();
            $this->error_message = $e;
            return false;
        }

        return true;
    }

    /**
    * Checks if an email address is on a given list with status "subscribed"
    *
    * @param string $list_id
    * @param string $email_address
    *
    * @return boolean
    */
    public function list_has_subscriber($list_id, $email_address)
    {
        try {
            $data = $this->get_api()->get_list_member($list_id, $email_address);
        } catch (MC4WP_API_Resource_Not_Found_Exception $e) {
            return false;
        }

        return ! empty($data->id) && $data->status === 'subscribed';
    }


    /**
    * Empty the Lists cache
    */
    public function empty_cache()
    {
        global $wpdb;

        delete_option('mc4wp_mailchimp_list_ids');
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'mc4wp_mailchimp_list_%'");
        delete_transient('mc4wp_list_counts');
    }

    /**
    * Get Mailchimp lists from cache.
    *
    * @param boolean deprecated parameter.
    * @return array
    */
    public function get_cached_lists()
    {
        return $this->get_lists(false);
    }

    /**
    * Get a specific Mailchimp list from local DB.
    *
    * @param string $list_id
    * @return MC4WP_MailChimp_List
    */
    public function get_cached_list($list_id)
    {
        return $this->get_list($list_id, false);
    }

    /**
    * Get Mailchimp lists, from cache or remote API.
    *
    * @param boolean $force Whether to force a result by hitting Mailchimp API
    * @return array
    */
    public function get_lists($force = true)
    {

        // first, get all list id's
        $list_ids = $this->get_list_ids($force);

        // then, fill $lists array with individual list details
        $lists = array();
        foreach ($list_ids as $list_id) {
            $list = $this->get_list($list_id, $force);
            $lists["{$list_id}"] = $list;
        }

        return $lists;
    }

    /**
    * @param string $list_id
    *
    * @return MC4WP_MailChimp_List
    */
    private function fetch_list($list_id)
    {
        try {
            $list_data = $this->get_api()->get_list($list_id, array( 'fields' => 'id,name,stats,web_id,campaign_defaults.from_name,campaign_defaults.from_email' ));

            // create local object
            $list = new MC4WP_MailChimp_List($list_data->id, $list_data->name);
            $list->subscriber_count = $list_data->stats->member_count;
            $list->web_id = $list_data->web_id;
            $list->campaign_defaults = $list_data->campaign_defaults;

            // get merge fields (if any)
            if ($list_data->stats->merge_field_count > 0) {
                $field_data = $this->get_api()->get_list_merge_fields($list->id, array( 'count' => 100, 'fields' => 'merge_fields.name,merge_fields.tag,merge_fields.type,merge_fields.required,merge_fields.default_value,merge_fields.options,merge_fields.public' ));

                // hydrate data into object
                foreach ($field_data as $data) {
                    $object = MC4WP_MailChimp_Merge_Field::from_data($data);
                    $list->merge_fields[] = $object;
                }
            }

            // get interest categories
            $interest_categories_data = $this->get_api()->get_list_interest_categories($list->id, array( 'count' => 100, 'fields' => 'categories.id,categories.title,categories.type' ));
            foreach ($interest_categories_data as $interest_category_data) {
                $interest_category = MC4WP_MailChimp_Interest_Category::from_data($interest_category_data);

                // fetch groups for this interest
                $interests_data = $this->get_api()->get_list_interest_category_interests($list->id, $interest_category->id, array( 'count' => 100, 'fields' => 'interests.id,interests.name'));
                foreach ($interests_data as $interest_data) {
                    $interest_category->interests[ (string) $interest_data->id ] = $interest_data->name;
                }

                $list->interest_categories[] = $interest_category;
            }
        } catch (MC4WP_API_Exception $e) {
            return null;
        }

        // save in option
        update_option('mc4wp_mailchimp_list_' . $list_id, $list, false);
        return $list;
    }

    /**
    * Get Mailchimp list ID's
    *
    * @param bool $force Force result by hitting Mailchimp API
    * @return array
    */
    public function get_list_ids($force = false)
    {
        $list_ids = (array) get_option('mc4wp_mailchimp_list_ids', array());

        if (empty($list_ids) && $force) {
            $list_ids = $this->fetch_list_ids();
        }

        return $list_ids;
    }

    /**
    * @return array
    */
    public function fetch_list_ids()
    {
        /**
         * Filters the amount of Mailchimp lists to fetch.
         *
         * If you increase this, it might be necessary to increase your PHP configuration to allow for a higher max_execution_time.
         *
         * @param int
         */
        $limit = apply_filters('mc4wp_mailchimp_list_limit', 200);

        try {
            $lists_data = $this->get_api()->get_lists(array( 'count' => $limit, 'fields' => 'lists.id' ));
        } catch (MC4WP_API_Exception $e) {
            return array();
        }

        $list_ids = wp_list_pluck($lists_data, 'id');

        // store list id's
        update_option('mc4wp_mailchimp_list_ids', $list_ids, false);

        return $list_ids;
    }

    /**
    * Fetch list ID's + lists from Mailchimp.
    *
    * @return bool
    */
    public function fetch_lists()
    {
        // try to increase time limit as this can take a while
        @set_time_limit(600);
        $list_ids = $this->fetch_list_ids();

        // randomize array order
        shuffle($list_ids);

        // fetch individual list details
        foreach ($list_ids as $list_id) {
            $list = $this->fetch_list($list_id);
        }

        return ! empty($list_ids);
    }

    /**
    * Get a given Mailchimp list
    *
    * @param string $list_id
    * @param bool $force Whether to force a result by hitting remote API
    * @return MC4WP_MailChimp_List
    */
    public function get_list($list_id, $force = false)
    {
        $list = get_option('mc4wp_mailchimp_list_' . $list_id);

        if (empty($list) && $force) {
            $list = $this->fetch_list($list_id);
        }

        if (empty($list)) {
            return new MC4WP_MailChimp_List($list_id, 'Unknown List');
        }

        return $list;
    }

    /**
    * Get an array of list_id => number of subscribers
    *
    * @return array
    */
    public function get_subscriber_counts()
    {
        $list_counts = get_transient('mc4wp_list_counts');
        if (is_array($list_counts)) {
            return $list_counts;
        }

        // transient not valid, fetch from API
        try {
            $lists = $this->get_api()->get_lists(array( 'count' => 100, 'fields' => 'lists.id,lists.stats' ));
        } catch (MC4WP_API_Exception $e) {
            return array();
        }

        $list_counts = array();

        // we got a valid response
        foreach ($lists as $list) {
            $list_counts["{$list->id}"] = $list->stats->member_count;
        }


        /**
        * Filters the cache time for Mailchimp lists configuration, in seconds. Defaults to 3600 seconds (1 hour).
        *
        * @since 2.0
        * @param int $seconds
        */
        $transient_lifetime = (int) apply_filters('mc4wp_lists_count_cache_time', 3600);

        // make sure transient lifetime is not lower than 60 seconds
        $transient_lifetime = max(60, $transient_lifetime);
        set_transient('mc4wp_list_counts', $list_counts, $transient_lifetime);

        return $list_counts;
    }


    /**
    * Returns number of subscribers on given lists.
    *
    * @param array|string $list_ids Array of list ID's, or single string.
    * @return int Total # subscribers for given lists.
    */
    public function get_subscriber_count($list_ids)
    {

        // make sure we're getting an array
        if (! is_array($list_ids)) {
            $list_ids = array( $list_ids );
        }

        // if we got an empty array, return 0
        if (empty($list_ids)) {
            return 0;
        }

        // get total number of subscribers for all lists
        $counts = $this->get_subscriber_counts();

        // start calculating subscribers count for all given list ID's combined
        $count = 0;
        foreach ($list_ids as $id) {
            $count += (isset($counts["{$id}"])) ? $counts["{$id}"] : 0;
        }

        /**
        * Filters the total subscriber_count for the given List ID's.
        *
        * @since 2.0
        * @param string $count
        * @param array $list_ids
        */
        return apply_filters('mc4wp_subscriber_count', $count, $list_ids);
    }

    /**
    * Resets error properties.
    */
    public function reset_error()
    {
        $this->error_message = '';
        $this->error_code = '';
    }

    /**
    * @return bool
    */
    public function has_error()
    {
        return ! empty($this->error_code);
    }

    /**
    * @return string
    */
    public function get_error_message()
    {
        return $this->error_message;
    }

    /**
    * @return string
    */
    public function get_error_code()
    {
        return $this->error_code;
    }

    /**
    * @return MC4WP_API_v3
    */
    private function get_api()
    {
        return mc4wp('api');
    }
}
