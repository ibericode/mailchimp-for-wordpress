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
	 * @return MC4WP_API_v3
	 */
	private function api() {
		return mc4wp('api');
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

		// if force_fallback is true, get lists from transient with longer expiration
		if( $force_fallback ) {
			$cached_lists = get_transient( $this->lists_transient_name . '_fallback' );
		}

		if( is_array( $cached_lists ) ) {
			return $cached_lists;
		}

		// transient was empty, get lists from MailChimp
		$api = $this->api();
		$lists_data = $api->get_lists();

		/**
		 * @var MC4WP_MailChimp_List[]
		 */
		$lists = array();

		// TODO: See if we can combine this into less API calls.....

		foreach ( $lists_data as $list_data ) {
			// create local object
			$list = new MC4WP_MailChimp_List( $list_data->id, $list_data->name );
			$list->subscriber_count = $list_data->stats->member_count;

			// add to array
			$lists["{$list->id}"] = $list;

			// get merge vars
			if( $list_data->stats->merge_field_count == 1 ) {
				continue;
			}

			$field_data = $api->get_list_merge_fields( $list->id );
			$list->merge_fields = array_map( array( 'MC4WP_MailChimp_Merge_Field', 'from_data' ), $field_data );

			// get interest groupings
			$groupings_data = $api->get_list_interest_categories( $list->id );
			foreach( $groupings_data as $grouping_data ) {
				$grouping = MC4WP_MailChimp_Interest_Category::from_data( $grouping_data );

				// fetch groups for this interest
				$interests_data = $api->get_list_interest_category_interests( $list->id, $grouping->id );
				foreach( $interests_data as $interest_data ) {
					$grouping->interests[ $interest_data->id ] = $interest_data->name;
				}

				$list->interest_categories[] = $grouping;
			}


		}

		// store lists in transients
		set_transient(  $this->lists_transient_name, $lists, ( 24 * 3600 * 2 ) ); // 2 days
		set_transient(  $this->lists_transient_name . '_fallback', $lists, 24 * 3600 * 30 ); // 30 days

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
		$api = $this->api();
		$lists = $api->get_lists();
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
		set_transient( $this->list_counts_transient_name, $list_counts, $transient_lifetime );

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


}
