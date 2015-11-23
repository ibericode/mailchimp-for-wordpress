<?php

/**
 * Class MC4WP_MailChimp
 *
 * @access private
 * @ignore
 */
class MC4WP_MailChimp {

	/**
	 * @var string
	 */
	protected $lists_transient_name = 'mc4wp_mailchimp_lists';

	/**
	 * @var string
	 */
	protected $list_counts_transient_name = 'mc4wp_list_counts';

	/**
	 * Empty the Lists cache
	 */
	public function empty_cache() {
		delete_transient( $this->lists_transient_name );
		delete_transient( $this->lists_transient_name . '_fallback' );
		delete_transient( $this->list_counts_transient_name );
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

		$cached_lists = get_transient( $this->lists_transient_name  );

		// if force_fallback is true, get lists from older transient
		if( $force_fallback ) {
			$cached_lists = get_transient( $this->lists_transient_name . '_fallback' );
		}

		if( is_array( $cached_lists ) ) {
			return $cached_lists;
		}

		// transient was empty, get lists from MailChimp
		$api = mc4wp('api');
		$lists_data = $api->get_lists();

		// if we did not get an array, something failed.
		// try fallback transient (if not tried before)
		if( ! is_array( $lists_data ) ) {
			$cached_lists = get_transient( $this->lists_transient_name . '_fallback' );

			if( is_array( $cached_lists ) ) {
				return $cached_lists;
			}

			// fallback transient was empty as well...
			return array();
		}

		/**
		 * @var MC4WP_MailChimp_List[]
		 */
		$lists = array();

		foreach ( $lists_data as $list_data ) {
			// create local object
			$list = new MC4WP_MailChimp_List( $list_data->id, $list_data->name, $list_data->web_id );
			$list->subscriber_count = $list_data->stats->member_count;

			// fill groupings if list has some
			if( $list_data->stats->grouping_count > 0 ) {
				// get interest groupings
				$groupings_data = $api->get_list_groupings( $list->id );
				if ( $groupings_data ) {
					$list->groupings = array_map( array( 'MC4WP_MailChimp_Grouping', 'from_data' ), $groupings_data );
				}
			}

			// add to array
			$lists["{$list->id}"] = $list;
		}

		// get merge vars for all lists at once
		$merge_vars_data = $api->get_lists_with_merge_vars( array_keys( $lists ) );
		if ( $merge_vars_data ) {
			foreach ( $merge_vars_data as $list ) {
				// add merge vars to list
				$lists["{$list->id}"]->merge_vars = array_map( array( 'MC4WP_MailChimp_Merge_Var', 'from_data' ), $list->merge_vars );
			}
		}

		// store lists in transients
		set_transient(  $this->lists_transient_name, $lists, ( 24 * 3600 ) ); // 1 day
		set_transient(  $this->lists_transient_name . '_fallback', $lists, 1209600 ); // 2 weeks

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
		$list_counts = get_transient( $this->list_counts_transient_name );
		if( is_array( $list_counts ) ) {
			return $list_counts;
		}

		// transient not valid, fetch from API
		$api = mc4wp('api');
		$lists = $api->get_lists();

		$list_counts = array();

		if ( is_array( $lists ) ) {

			// we got a valid response
			foreach ( $lists as $list ) {
				$list_counts["{$list->id}"] = $list->stats->member_count;
			}

			$seconds = 1200;

			/**
			 * Filters the cache time for MailChimp lists configuration. Defaults to 1200.
			 *
			 * @since 2.0
			 * @param int $seconds
			 */
			$transient_lifetime = (int) apply_filters( 'mc4wp_lists_count_cache_time', $seconds );
			set_transient( $this->list_counts_transient_name, $list_counts, $transient_lifetime );

			// bail
			return $list_counts;
		}

		// api call failed, get from stored lists
		$lists = $this->get_lists( true );
		foreach( $lists as $list ) {
			$list_counts["{$list->id}"] = $list->subscriber_count;
		}

		return $list_counts;
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
		 * @param int $count
		 * @param array $list_ids
		 */
		return apply_filters( 'mc4wp_subscriber_count', $count, $list_ids );
	}


}
