<?php

/**
 * Class MC4WP_MailChimp
 *
 * @access private
 * @ignore
 */
class MC4WP_MailChimp {

	/**
	 * @var MC4WP_API_v3
	 */
	public $api;

	/**
	 * @var string
	 */
	public $error_code = '';

	/**
	 * @var string
	 */
	public $error_message = '';

	/**
	 * MC4WP_MailChimp constructor.
	 */
	public function __construct() {
		$this->api = mc4wp( 'api' );
	}

	/**
	 *
	 * TODO: Force re-sending double opt-in email by deleting pending subscribers from list first.
	 *
	 * Sends a subscription request to the MailChimp API
	 *
	 * @param string  $list_id           The list id to subscribe to
	 * @param string  $email_address             The email address to subscribe
	 * @param array    $args
	 * @param boolean $update_existing   Update information if this email is already on list?
	 * @param boolean $replace_interests Replace interest groupings, only if update_existing is true.
	 *
	 * @return object
	 */
	public function list_subscribe( $list_id, $email_address, array $args = array(), $update_existing = false, $replace_interests = true ) {
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
			$existing_member_data = $this->api->get_list_member( $list_id, $email_address );

			if( $existing_member_data->status === 'subscribed' ) {
			    $already_on_list = true;

				// if we're not supposed to update, bail.
				if( ! $update_existing ) {
					$this->error_code = 214;
					$this->error_message = 'That subscriber already exists.';
					return null;
				}

				$args['status'] = 'subscribed';

				// this key only exists if list actually has interests
				if( isset( $existing_member_data->interests ) ) {
					$existing_interests = (array) $existing_member_data->interests;

					// if replace, assume all existing interests disabled
					if( $replace_interests ) {
						$existing_interests = array_fill_keys( array_keys( $existing_interests ), false );
					}

					$args['interests'] = $args['interests'] + $existing_interests;
				}
			} else {
			    // delete list member so we can re-add it...
			    $this->api->delete_list_member( $list_id, $email_address );
            }
		} catch ( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// subscriber does not exist (not an issue in this case)
		} catch( MC4WP_API_Exception $e ) {
            // other errors.
            $this->error_code = $e->getCode();
            $this->error_message = $e;
			return null;
		}

		try {
			$data = $this->api->add_list_member( $list_id, $args );
		} catch ( MC4WP_API_Exception $e ) {
			$this->error_code = $e->getCode();
			$this->error_message = $e;
			return null;
		}

		$data->was_already_on_list = $already_on_list;

		return $data;
	}

	/**
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return boolean
	 */
	public function list_unsubscribe( $list_id, $email_address ) {
		$this->reset_error();

		try {
			$this->api->update_list_member( $list_id, $email_address, array( 'status' => 'unsubscribed' ) );
		} catch( MC4WP_API_Resource_Not_Found_Exception $e ) {
		    // if email wasn't even on the list: great.
		    return true;
        } catch( MC4WP_API_Exception $e ) {
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
	public function list_has_subscriber( $list_id, $email_address ) {
		$data = $this->api->get_list_member( $list_id, $email_address );
		return ! empty( $data->id ) && $data->status === 'subscribed';
	}


	/**
	 * Empty the Lists cache
	 */
	public function empty_cache() {
		delete_transient( 'mc4wp_mailchimp_lists_v3' );
		delete_transient( 'mc4wp_mailchimp_lists_v3_fallback' );
		delete_transient( 'mc4wp_list_counts' );
	}

	/**
	 * Get MailChimp lists
	 * Try cache first, then try API, then try fallback cache.
	 *
	 * @param bool $force_fallback
	 *
	 * @return array
	 */
	public function get_lists( $force_fallback = false ) {

		$cached_lists = get_transient( 'mc4wp_mailchimp_lists_v3'  );

		// if force_fallback is true, get lists from transient with longer expiration
		if( $force_fallback ) {
			$cached_lists = get_transient( 'mc4wp_mailchimp_lists_v3_fallback' );
		}

		if( is_array( $cached_lists ) ) {
			return $cached_lists;
		}

		// TODO: Move this to the background via a queue.

		// try to increase time limit as this can take a while
		@set_time_limit(600);

		try{
			$lists_data = $this->api->get_lists( array( 'count' => 500, 'fields' => 'lists.id' ) );
        } catch( MC4WP_API_Exception $e ) {
            return array();
        }

        $list_ids = wp_list_pluck( $lists_data, 'id' );

        /**
         * @var MC4WP_MailChimp_List[]
         */
        $lists = array();

        foreach ( $list_ids as $list_id ) {

            // if any API call for this list fails, simply move on to next list.
            try {
                $list_data = $this->api->get_list( $list_id, array( 'fields' => 'id,name,stats' ) );

                // create local object
                $list = new MC4WP_MailChimp_List( $list_data->id, $list_data->name );
                $list->subscriber_count = $list_data->stats->member_count;

                // parse web_id from the "link" response header
                $headers = $this->api->get_last_response_headers();
                $link_header = $headers['link'];
                preg_match( '/\?id=(\d+)/', $link_header, $matches );
                if( ! empty( $matches[1] ) ) {
                    $list->web_id = $matches[1];
                };

                // add to array
                $lists["{$list->id}"] = $list;

                // get merge fields (if any)
                if( $list_data->stats->merge_field_count > 0 ) {
                    $field_data = $this->api->get_list_merge_fields( $list->id, array( 'count' => 100, 'fields' => 'merge_fields.name,merge_fields.tag,merge_fields.type,merge_fields.required,merge_fields.default_value,merge_fields.options,merge_fields.public' ) );

                    // hydrate data into object
                    foreach( $field_data as $data ) {
                        $object = MC4WP_MailChimp_Merge_Field::from_data( $data );
                        $list->merge_fields[] = $object;
                    }
                }

                // get interest categories
                $interest_categories_data = $this->api->get_list_interest_categories( $list->id, array( 'count' => 100, 'fields' => 'categories.id,categories.title,categories.type' ) );
                foreach( $interest_categories_data as $interest_category_data ) {
                    $interest_category = MC4WP_MailChimp_Interest_Category::from_data( $interest_category_data );

                    // fetch groups for this interest
                    $interests_data = $this->api->get_list_interest_category_interests( $list->id, $interest_category->id, array( 'count' => 100, 'fields' => 'interests.id,interests.name') );
                    foreach( $interests_data as $interest_data ) {
                        $interest_category->interests[ $interest_data->id ] = $interest_data->name;
                    }

                    $list->interest_categories[] = $interest_category;
                }
            } catch( MC4WP_API_Exception $e ) {
                continue;
            }

        }

		// store lists in transients
		set_transient( 'mc4wp_mailchimp_lists_v3', $lists, ( 60 * 60 * 24 * 3 ) ); // 3 days
		set_transient( 'mc4wp_mailchimp_lists_v3_fallback', $lists, 60 * 60 * 24 * 30 ); // 30 days

		return $lists;
	}

	/**
	 * Get a given MailChimp list
	 *
	 * @param int $list_id
	 * @param bool $force_fallback
	 *
	 * @return MC4WP_MailChimp_List
	 */
	public function get_list( $list_id, $force_fallback = false ) {
		$lists = $this->get_lists( $force_fallback );

		if( isset( $lists[$list_id] ) ) {
			return $lists[$list_id];
		}

		// return dummy list object
		return new MC4WP_MailChimp_List( '', 'Unknown List' );
	}

	/**
	 * Get an array of list_id => number of subscribers
	 *
	 * @return array
	 */
	public function get_subscriber_counts() {

		// get from transient
		$list_counts = get_transient( 'mc4wp_list_counts' );
		if( is_array( $list_counts ) ) {
			return $list_counts;
		}

		// transient not valid, fetch from API
        try {
		    $lists = $this->api->get_lists(  array( 'count' => 100, 'fields' => 'lists.id,lists.stats' ) );
        } catch( MC4WP_API_Exception $e ) {
            return array();
        }

		$list_counts = array();

		// we got a valid response
		foreach ( $lists as $list ) {
			$list_counts["{$list->id}"] = $list->stats->member_count;
		}

		$seconds = 3600;

		/**
		 * Filters the cache time for MailChimp lists configuration, in seconds. Defaults to 3600 seconds (1 hour).
		 *
		 * @since 2.0
		 * @param int $seconds
		 */
		$transient_lifetime = (int) apply_filters( 'mc4wp_lists_count_cache_time', $seconds );
		set_transient( 'mc4wp_list_counts', $list_counts, $transient_lifetime );

		// bail
		return $list_counts;
	}


	/**
	 * Returns number of subscribers on given lists.
	 *
	 * @param array|string $list_ids Array of list ID's, or single string.
	 * @return int Total # subscribers for given lists.
	 */
	public function get_subscriber_count( $list_ids ) {

		// make sure we're getting an array
		if( ! is_array( $list_ids ) ) {
			$list_ids = array( $list_ids );
		}

		// if we got an empty array, return 0
		if( empty( $list_ids ) ) {
			return 0;
		}

		// get total number of subscribers for all lists
		$counts = $this->get_subscriber_counts();

		// start calculating subscribers count for all given list ID's combined
		$count = 0;
		foreach ( $list_ids as $id ) {
			$count += ( isset( $counts[$id] ) ) ? $counts[$id] : 0;
		}

		/**
		 * Filters the total subscriber_count for the given List ID's.
		 *
		 * @since 2.0
		 * @param string $count
		 * @param array $list_ids
		 */
		return apply_filters( 'mc4wp_subscriber_count', $count, $list_ids );
	}

	/**
	 * Resets error properties.
	 */
	public function reset_error() {
		$this->error_message = '';
		$this->error_code = '';
	}

	/**
	 * @return bool
	 */
	public function has_error() {
		return ! empty( $this->error_code );
	}

	/**
	 * @return string
	 */
	public function get_error_message() {
		return $this->error_message;
	}

	/**
	 * @return string
	 */
	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * @return MC4WP_API_v3
	 */
	private function api() {
		return mc4wp('api');
	}


}
