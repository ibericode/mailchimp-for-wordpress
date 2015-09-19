<?php

class MC4WP_MailChimp {

	/**
	 * @var string
	 */
	protected $transient_name = 'mc4wp_mailchimp_lists';

	/**
	 * Empty the Lists cache
	 */
	public function empty_cache() {
		delete_transient( $this->transient_name );
		delete_transient( $this->transient_name . '_fallback' );
	}

	/**
	 * Get MailChimp lists
	 * Try cache first, then try API, then try fallback cache.
	 *
	 * @param bool $force_refresh
	 * @param bool $force_fallback
	 *
	 * @return array
	 */
	public function get_lists( $force_refresh = false, $force_fallback = false ) {

		if( $force_refresh ) {
			$this->empty_cache();
		}

		$cached_lists = get_transient( $this->transient_name  );

		// if force_fallback is true, get lists from older transient
		if( $force_fallback ) {
			$cached_lists = get_transient( $this->transient_name . '_fallback' );
		}

		// got lists? if not, proceed with API call.
		if( ! is_array( $cached_lists ) ) {

			// make api request for lists
			$api = mc4wp_get_api();
			$lists_data = $api->get_lists();

			if ( is_array( $lists_data ) ) {

				$lists = array();

				foreach ( $lists_data as $list ) {

					$lists["{$list->id}"] = (object) array(
						'id' => $list->id,
						'name' => $list->name,
						'subscriber_count' => $list->stats->member_count,
						'merge_vars' => array(),
						'interest_groupings' => array()
					);

					// only get interest groupings if list has some
					if( $list->stats->grouping_count > 0 ) {
						// get interest groupings
						$groupings_data = $api->get_list_groupings( $list->id );
						if ( $groupings_data ) {
							$lists["{$list->id}"]->interest_groupings = array_map( array( $this, 'strip_unnecessary_grouping_properties' ), $groupings_data );
						}
					}

				}

				// get merge vars for all lists at once
				$merge_vars_data = $api->get_lists_with_merge_vars( array_keys( $lists ) );
				if ( $merge_vars_data ) {
					foreach ( $merge_vars_data as $list ) {
						// add merge vars to list
						$lists["{$list->id}"]->merge_vars = array_map( array( $this, 'strip_unnecessary_merge_vars_properties' ), $list->merge_vars );
					}
				}

				// store lists in transients
				set_transient(  $this->transient_name, $lists, ( 24 * 3600 ) ); // 1 day
				set_transient(  $this->transient_name . '_fallback', $lists, 1209600 ); // 2 weeks

				return $lists;
			} else {
				// api request failed, get fallback data (with longer lifetime)
				$cached_lists = get_transient( $this->transient_name . '_fallback' );

				if ( ! is_array( $cached_lists ) ) {
					return false;
				}
			}

		}

		return $cached_lists;
	}

	/**
	 * Get a given MailChimp list
	 *
	 * @param int $list_id
	 * @param bool $force_renewal
	 * @param bool $force_fallback
	 *
	 * @return bool
	 */
	public function get_list( $list_id, $force_renewal = false, $force_fallback = false ) {
		$lists = $this->get_lists( $force_renewal, $force_fallback );

		if( isset( $lists[$list_id] ) ) {
			return $lists[$list_id];
		}

		return false;
	}

	/**
	 * Get the name of the MailChimp list with the given ID.
	 *
	 * @param int $id
	 * @return string
	 */
	public function get_list_name( $id ) {
		$list = $this->get_list( $id );

		if( is_object( $list ) && isset( $list->name ) ) {
			return $list->name;
		}

		return '';
	}

	/**
	 * Get the interest grouping object for a given list.
	 *
	 * @param string $list_id ID of MailChimp list that contains the grouping
	 * @param string $grouping_id ID of the Interest Grouping
	 *
	 * @return object|null
	 */
	public function get_list_grouping( $list_id, $grouping_id ) {
		$list = $this->get_list( $list_id, false, true );

		if( is_object( $list ) && isset( $list->interest_groupings ) ) {
			foreach( $list->interest_groupings as $grouping ) {

				if( $grouping->id !== $grouping_id ) {
					continue;
				}

				return $grouping;
			}
		}

		return null;
	}

	/**
	 * Get the name of a list grouping by its ID
	 *
	 * @param $list_id
	 * @param $grouping_id
	 *
	 * @return string
	 */
	public function get_list_grouping_name( $list_id, $grouping_id ) {

		$grouping = $this->get_list_grouping( $list_id, $grouping_id );
		if( isset( $grouping->name ) ) {
			return $grouping->name;
		}

		return '';
	}

	/**
	 * Get the group object for a group in an interest grouping
	 *
	 * @param string $list_id ID of MailChimp list that contains the grouping
	 * @param string $grouping_id ID of the Interest Grouping containing the group
	 * @param string $group_id_or_name ID or name of the Group
	 * @return object|null
	 */
	public function get_list_grouping_group( $list_id, $grouping_id, $group_id_or_name ) {
		$grouping = $this->get_list_grouping( $list_id, $grouping_id );
		if( is_object( $grouping ) && isset( $grouping->groups ) ) {
			foreach( $grouping->groups as $group ) {

				if( $group->id == $group_id_or_name || $group->name === $group_id_or_name ) {
					return $group;
				}

			}
		}

		return null;
	}

	/**
	 * Returns number of subscribers on given lists.
	 *
	 * @param array $list_ids Array of list id's.
	 * @return int Sum of subscribers for given lists.
	 */
	public function get_subscriber_count( $list_ids ) {

		// don't count when $list_ids is empty or not an array
		if( ! is_array( $list_ids ) || count( $list_ids ) === 0 ) {
			return 0;
		}

		$list_counts = get_transient( 'mc4wp_list_counts' );

		if( false === $list_counts ) {
			// make api call
			$api = mc4wp_get_api();
			$lists = $api->get_lists();
			$list_counts = array();

			if ( is_array( $lists ) ) {

				foreach ( $lists as $list ) {
					$list_counts["{$list->id}"] = $list->stats->member_count;
				}

				$transient_lifetime = apply_filters( 'mc4wp_lists_count_cache_time', 1200 ); // 20 mins by default

				set_transient( 'mc4wp_list_counts', $list_counts, $transient_lifetime );
				set_transient( 'mc4wp_list_counts_fallback', $list_counts, 86400 ); // 1 day
			} else {
				// use fallback transient
				$list_counts = get_transient( 'mc4wp_list_counts_fallback' );
				if ( false === $list_counts ) {
					return 0;
				}
			}
		}

		// start calculating subscribers count for all list combined
		$count = 0;
		foreach ( $list_ids as $id ) {
			$count += ( isset( $list_counts[$id] ) ) ? $list_counts[$id] : 0;
		}

		return apply_filters( 'mc4wp_subscriber_count', $count );
	}

	/**
	 * Build the group array object which will be stored in cache
	 *
	 * @param object $group
	 * @return object
	 */
	public function strip_unnecessary_group_properties( $group ) {
		return (object) array(
			'name' => $group->name,
		);
	}

	/**
	 * Build the groupings array object which will be stored in cache
	 *
	 * @param object $grouping
	 * @return object
	 */
	public function strip_unnecessary_grouping_properties( $grouping ) {
		return (object) array(
			'id' => $grouping->id,
			'name' => $grouping->name,
			'groups' => array_map( array( $this, 'strip_unnecessary_group_properties' ), $grouping->groups ),
			'form_field' => $grouping->form_field,
		);
	}

	/**
	 * Build the merge_var array object which will be stored in cache
	 *
	 * @param object $merge_var
	 * @return object
	 */
	public function strip_unnecessary_merge_vars_properties( $merge_var ) {
		$array = array(
			'name' => $merge_var->name,
			'field_type' => $merge_var->field_type,
			'req' => $merge_var->req,
			'tag' => $merge_var->tag,
		);

		if ( isset( $merge_var->choices ) ) {
			$array['choices'] = $merge_var->choices;
		}

		return (object) $array;

	}

	/**
	 * Get the name of a list field by its merge tag
	 *
	 * @param $list_id
	 * @param $tag
	 *
	 * @return string
	 */
	public function get_list_field_name_by_tag( $list_id, $tag ) {
		// try default fields
		switch( $tag ) {
			case 'EMAIL':
				return __( 'Email address', 'mailchimp-for-wp' );
				break;
			case 'OPTIN_IP':
				return __( 'IP Address', 'mailchimp-for-wp' );
				break;
		}
		// try to find field in list
		$list = $this->get_list( $list_id, false, true );
		if( is_object( $list ) && isset( $list->merge_vars ) ) {
			// try list merge vars first
			foreach( $list->merge_vars as $field ) {
				if( $field->tag !== $tag ) {
					continue;
				}
				return $field->name;
			}
		}
		return '';
	}

}
