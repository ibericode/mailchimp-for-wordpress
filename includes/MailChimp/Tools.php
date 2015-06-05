<?php

class MC4WP_MailChimp_Tools {

	/**
	 * Get MailChimp lists
	 * Try cache first, then try API, then try fallback cache.
	 *
	 * @api
	 * @param bool $force_renewal
	 * @param bool $force_fallback
	 *
	 * @return array
	 */
	public static function get_lists( $force_renewal = false, $force_fallback = false ) {

		$lists_raw = get_transient( 'mc4wp_mailchimp_lists' );

		// if force_fallback is true, get lists from older transient
		if( true === $force_fallback ) {
			$lists_raw = get_transient( 'mc4wp_mailchimp_lists_fallback' );
		}

		if ( true === $force_renewal || empty( $lists_raw ) ) {

			// make api request for lists
			$api = mc4wp_get_api();
			$lists_data = $api->get_lists();

			if ( is_array( $lists_data ) ) {

				$lists_raw = array();

				foreach ( $lists_data as $list ) {

					$lists_raw["{$list->id}"] = (object) array(
						'id' => $list->id,
						'name' => $list->name,
						'subscriber_count' => $list->stats->member_count,
						'merge_vars' => array(),
						'interest_groupings' => array(),
					);

					// get interest groupings
					$groupings_data = $api->get_list_groupings( $list->id );
					if ( $groupings_data ) {
						$lists_raw["{$list->id}"]->interest_groupings = array_map( array( 'MC4WP_MailChimp_Tools', 'strip_unnecessary_grouping_properties' ), $groupings_data );
					}

				}


				// get merge vars for all lists at once
				$merge_vars_data = $api->get_lists_with_merge_vars( array_keys( $lists_raw ) );
				if ( $merge_vars_data ) {
					foreach ( $merge_vars_data as $list ) {
						// add merge vars to list
						$lists_raw["{$list->id}"]->merge_vars = array_map( array( 'MC4WP_MailChimp_Tools', 'strip_unnecessary_merge_vars_properties' ), $list->merge_vars );
					}
				}

				// store lists in transients
				set_transient( 'mc4wp_mailchimp_lists', $lists_raw, ( 24 * 3600 ) ); // 1 day
				set_transient( 'mc4wp_mailchimp_lists_fallback', $lists_raw, 1209600 ); // 2 weeks
			} else {
				// api request failed, get fallback data (with longer lifetime)
				$lists_raw = get_transient( 'mc4wp_mailchimp_lists_fallback' );

				if ( ! $lists_raw ) {
					return array();
				}
			}
		}

		// create List objects
		$lists = array();
		foreach( $lists_raw as $list_id => $list_data ) {
			$lists[$list_id] = new MC4WP_MailChimp_List( $list_id, $list_data );
		}

		return $lists;
	}

	/**
	 * Get a given MailChimp list
	 *
	 * @api
	 * @param int $list_id
	 * @param bool $force_renewal
	 * @param bool $force_fallback
	 *
	 * @return bool
	 */
	public static function get_list( $list_id, $force_renewal = false, $force_fallback = false ) {

		$lists = self::get_lists( $force_renewal, $force_fallback );

		if( isset( $lists[$list_id] ) ) {
			return $lists[$list_id];
		}

		return new MC4WP_MailChimp_List( $list_id );
	}

	/**
	 * Returns number of subscribers on given lists.
	 *
	 * @api
	 * @param array $list_ids of list id's.
	 * @return int Sum of subscribers for given lists.
	 */
	public static function get_subscriber_count( $list_ids ) {

		// don't count when $list_ids is empty or not an array
		if( ! is_array( $list_ids ) || count( $list_ids ) === 0 ) {
			return 0;
		}

		$list_counts = get_transient( 'mc4wp_list_counts' );

		if ( false === $list_counts ) {

			// make api call
			$api = mc4wp_get_api();
			$lists = $api->get_lists();
			$list_counts = array();

			if ( is_array( $lists ) ) {

				foreach ( $lists as $list ) {
					$list_counts["{$list->id}"] = $list->stats->member_count;
				}

				/**
				 * @filter `mc4wp_lists_count_cache_time`
				 * @expects int
				 *
				 * Sets the amount of time the subscriber count for lists should be stored
				 */
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
	 * @return object
	 */
	public static function strip_unnecessary_group_properties( $group ) {
		return (object) array(
			'name' => $group->name
		);
	}

	/**
	 * Build the groupings array object which will be stored in cache
	 * @return object
	 */
	public static function strip_unnecessary_grouping_properties( $grouping ) {
		return (object) array(
			'id' => $grouping->id,
			'name' => $grouping->name,
			'groups' => array_map( array( 'MC4WP_MailChimp_Tools', 'strip_unnecessary_group_properties' ), $grouping->groups ),
			'form_field' => $grouping->form_field,
		);
	}

	/**
	 * Build the merge_var array object which will be stored in cache
	 * @return object
	 */
	public static function strip_unnecessary_merge_vars_properties( $merge_var ) {
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

}
