<?php

/**
 * Internal class for dealing with common API requests.
 * Please don't use directly as this code can be subject to backwards incompatible changes.
 *
* @access private
* @ignore
* @internal
*/
class MC4WP_MailChimp {


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
	* @param bool $update_existing   Update information if this email is already on list?
	* @param bool $replace_interests Replace interest groupings, only if update_existing is true.
	* @return object
	* @throws Exception
	*/
	public function list_subscribe( $list_id, $email_address, array $args = array(), $update_existing = false, $replace_interests = true ) {
		$this->reset_error();
		$default_args         = array(
			'status'        => 'pending',
			'email_address' => $email_address,
		);
		$existing_member_data = null;

		// setup default args
		$args = array_merge( $default_args, $args );
		$api  = $this->get_api();

		// first, check if subscriber is already on the given list
		try {
			$existing_member_data = $api->get_list_member( $list_id, $email_address );

			if ( $existing_member_data->status === 'subscribed' ) {

				// if we're not supposed to update, bail.
				if ( ! $update_existing ) {
					$this->error_code    = 214;
					$this->error_message = 'That subscriber already exists.';
					return null;
				}

				$args['status'] = 'subscribed';

				// this key only exists if list actually has interests
				if ( isset( $existing_member_data->interests ) ) {
					$existing_interests = (array) $existing_member_data->interests;

					// if replace, assume all existing interests disabled
					if ( $replace_interests ) {
						$existing_interests = array_fill_keys( array_keys( $existing_interests ), false );
					}

					// TODO: Use array_replace here (PHP 5.3+)
					$new_interests     = $args['interests'];
					$args['interests'] = $existing_interests;
					foreach ( $new_interests as $interest_id => $interest_status ) {
						$args['interests'][ "{$interest_id}" ] = $interest_status;
					}
				}
			} elseif ( $args['status'] === 'pending' && $existing_member_data->status === 'pending' ) {
				// this ensures that a new double opt-in email is send out
				$api->update_list_member(
					$list_id,
					$email_address,
					array(
						'status' => 'unsubscribed',
					)
				);
			}
		} catch ( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// subscriber does not exist (not an issue in this case)
		} catch ( MC4WP_API_Exception $e ) {
			// other errors.
			$this->error_code    = $e->getCode();
			$this->error_message = $e;
			return null;
		}

		try {
			if ( $existing_member_data ) {
				$data                      = $api->update_list_member( $list_id, $email_address, $args );
				$data->was_already_on_list = $existing_member_data->status === 'subscribed';
			} else {
				$data                      = $api->add_new_list_member( $list_id, $args );
				$data->was_already_on_list = false;
			}
		} catch ( MC4WP_API_Exception $e ) {
			$this->error_code    = $e->getCode();
			$this->error_message = $e;
			return null;
		}

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
	public function list_unsubscribe( $list_id, $email_address ) {
		$this->reset_error();

		try {
			$this->get_api()->update_list_member( $list_id, $email_address, array( 'status' => 'unsubscribed' ) );
		} catch ( MC4WP_API_Resource_Not_Found_Exception $e ) {
			// if email wasn't even on the list: great.
			return true;
		} catch ( MC4WP_API_Exception $e ) {
			$this->error_code    = $e->getCode();
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
	* @throws Exception
	*/
	public function list_has_subscriber( $list_id, $email_address ) {
		try {
			$data = $this->get_api()->get_list_member( $list_id, $email_address );
		} catch ( MC4WP_API_Resource_Not_Found_Exception $e ) {
			return false;
		}

		return ! empty( $data->id ) && $data->status === 'subscribed';
	}

	/**
	 * @param string $list_id
	 * @return array
	 * @throws Exception
	 */
	public function get_list_merge_fields( $list_id ) {
		$transient_key = sprintf( 'mc4wp_list_%s_mf', $list_id );
		$cached        = get_transient( $transient_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$api = $this->get_api();

		try {
			// fetch list merge fields
			$merge_fields = $api->get_list_merge_fields(
				$list_id,
				array(
					'count'  => 100,
					'fields' => 'merge_fields.name,merge_fields.tag,merge_fields.type,merge_fields.required,merge_fields.default_value,merge_fields.options,merge_fields.public',
				)
			);
		} catch ( MC4WP_API_Exception $e ) {
			return array();
		}

		// add EMAIL field
		array_unshift(
			$merge_fields,
			(object) array(
				'tag'      => 'EMAIL',
				'name'     => __( 'Email address', 'mailchimp-for-wp' ),
				'required' => true,
				'type'     => 'email',
				'options'  => array(),
				'public'   => true,
			)
		);

		set_transient( $transient_key, $merge_fields, HOUR_IN_SECONDS * 24 );
		return $merge_fields;
	}

	/**
	 * @param string $list_id
	 * @return array
	 * @throws Exception
	 */
	public function get_list_interest_categories( $list_id ) {
		$transient_key = sprintf( 'mc4wp_list_%s_ic', $list_id );
		$cached        = get_transient( $transient_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$api = $this->get_api();

		try {
			// fetch list interest categories
			$interest_categories = $api->get_list_interest_categories(
				$list_id,
				array(
					'count'  => 100,
					'fields' => 'categories.id,categories.title,categories.type',
				)
			);
		} catch ( MC4WP_API_Exception $e ) {
			return array();
		}

		foreach ( $interest_categories as $interest_category ) {
			$interest_category->interests = array();

			try {
				// fetch groups for this interest
				$interests_data = $api->get_list_interest_category_interests(
					$list_id,
					$interest_category->id,
					array(
						'count'  => 100,
						'fields' => 'interests.id,interests.name',
					)
				);
				foreach ( $interests_data as $interest_data ) {
					$interest_category->interests[ (string) $interest_data->id ] = $interest_data->name;
				}
			} catch ( MC4WP_API_Exception $e ) {
				// ignore
			}
		}

		set_transient( $transient_key, $interest_categories, HOUR_IN_SECONDS * 24 );
		return $interest_categories;
	}

	/**
	 * Get Mailchimp lists, from cache or remote API.
	 *
	 * @param boolean $skip_cache Whether to force a result by hitting Mailchimp API
	 * @return array
	 */
	public function get_lists( $skip_cache = false ) {
		$cache_key = 'mc4wp_mailchimp_lists';
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && ! $skip_cache ) {
			return $cached;
		}

		$lists = $this->fetch_lists();

		/**
		 * Filters the cache time for Mailchimp lists configuration, in seconds. Defaults to 24 hours.
		 */
		$cache_ttl = (int) apply_filters( 'mc4wp_lists_count_cache_time', HOUR_IN_SECONDS * 24 );

		// make sure cache ttl is not lower than 60 seconds
		$cache_ttl = max( 60, $cache_ttl );
		set_transient( $cache_key, $lists, $cache_ttl );
		return $lists;
	}

	private function fetch_lists() {
		/**
		 * Filters the amount of Mailchimp lists to fetch.
		 *
		 * If you increase this, it might be necessary to increase your PHP configuration to allow for a higher max_execution_time.
		 *
		 * @param int
		 */
		$limit = apply_filters( 'mc4wp_mailchimp_list_limit', 200 );

		try {
			$lists_data = $this->get_api()->get_lists(
				array(
					'count'  => $limit,
					'fields' => 'lists.id,lists.name,lists.stats,lists.web_id',
				)
			);
		} catch ( MC4WP_API_Exception $e ) {
			return array();
		}

		// key by list ID
		$lists = array();
		foreach ( $lists_data as $list_data ) {
			$lists[ "$list_data->id" ] = $list_data;
		}

		return $lists;
	}

	/**
	 * @param string $list_id
	 * @return object|null
	 */
	public function get_list( $list_id ) {
		$lists = $this->get_lists();
		return isset( $lists[ "$list_id" ] ) ? $lists[ "$list_id" ] : null;
	}

	/**
	* Fetch lists data from Mailchimp.
	*/
	public function refresh_lists() {
		$lists = $this->get_lists( true );

		foreach ( $lists as $list_id => $list ) {
			$transient_key = sprintf( 'mc4wp_list_%s_mf', $list_id );
			delete_transient( $transient_key );

			$transient_key = sprintf( 'mc4wp_list_%s_ic', $list_id );
			delete_transient( $transient_key );
		}

		return ! empty( $lists );
	}


	/**
	* Returns number of subscribers on given lists.
	*
	* @param array|string $list_ids Array of list ID's, or single string.
	* @return int Total # subscribers for given lists.
	*/
	public function get_subscriber_count( $list_ids ) {
		// make sure we're getting an array
		if ( ! is_array( $list_ids ) ) {
			$list_ids = array( $list_ids );
		}

		// if we got an empty array, return 0
		if ( empty( $list_ids ) ) {
			return 0;
		}

		$lists = $this->get_lists();

		// start calculating subscribers count for all given list ID's combined
		$count = 0;
		foreach ( $list_ids as $list_id ) {

			if ( ! isset( $lists[ "$list_id" ] ) ) {
				continue;
			}

			$list   = $lists[ "$list_id" ];
			$count += $list->stats->member_count;
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
		$this->error_code    = '';
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
	* @return MC4WP_API_V3
	* @throws Exception
	*/
	private function get_api() {
		return mc4wp( 'api' );
	}
}
