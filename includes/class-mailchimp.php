<?php
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_MailChimp {

	/**
	 * Get MailChimp lists
	 * Try cache first, then try API, then try fallback cache.
	 *
	 * @return array
	 */
	public function get_lists( $force_renewal = false ) {

		$cached_lists = get_transient( 'mc4wp_mailchimp_lists' );

		if ( true === $force_renewal || false === $cached_lists || empty( $cached_lists ) ) {

			// make api request for lists
			$api = mc4wp_get_api();
			$lists = array();

			$lists_data = $api->get_lists();

			if ( $lists_data ) {

				$lists = array();

				foreach ( $lists_data as $list ) {

					$lists["{$list->id}"] = (object) array(
						'id' => $list->id,
						'name' => $list->name,
						'subscriber_count' => $list->stats->member_count,
						'merge_vars' => array(),
						'interest_groupings' => array()
					);

					// get interest groupings
					$groupings_data = $api->get_list_groupings( $list->id );
					if ( $groupings_data ) {
						$lists["{$list->id}"]->interest_groupings = array_map( array( $this, 'strip_unnecessary_grouping_properties' ), $groupings_data );
					}

				}


				// get merge vars for all lists at once
				$merge_vars_data = $api->get_lists_with_merge_vars( array_keys($lists) );
				if ( $merge_vars_data ) {
					foreach ( $merge_vars_data as $list ) {
						// add merge vars to list
						$lists["{$list->id}"]->merge_vars = array_map( array( $this, 'strip_unnecessary_merge_vars_properties' ), $list->merge_vars );
					}
				}

				// store lists in transients
				set_transient( 'mc4wp_mailchimp_lists', $lists, ( 24 * 3600 ) ); // 1 day
				set_transient( 'mc4wp_mailchimp_lists_fallback', $lists, 1209600 ); // 2 weeks
				return $lists;
			} else {
				// api request failed, get fallback data (with longer lifetime)
				$cached_lists = get_transient( 'mc4wp_mailchimp_lists_fallback' );

				if ( ! $cached_lists ) {
					return array();
				}
			}

		}

		return $cached_lists;
	}

	/**
	 * Get a given MailChimp list
	 *
	 * @param int $list_id
	 *
	 * @return bool
	 */
	public function get_list( $list_id ) {
		$lists = $this->get_lists();

		foreach( $lists as $list ) {
			if( $list->id === $list_id ) {
				return $list;
			}
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

		if( is_object( $list ) ) {
			return $list->name;
		}

		return '';
	}

	/**
	 * Build the group array object which will be stored in cache
	 * @return object
	 */
	public function strip_unnecessary_group_properties( $group ) {
		return (object) array(
			'name' => $group->name
		);
	}

	/**
	 * Build the groupings array object which will be stored in cache
	 * @return object
	 */
	public function strip_unnecessary_grouping_properties( $grouping ) {
		return (object) array(
			'id' => $grouping->id,
			'name' => $grouping->name,
			'groups' => array_map( array( $this, 'strip_unnecessary_group_properties' ), $grouping->groups ),
			'form_field' => $grouping->form_field
		);
	}

	/**
	 * Build the merge_var array object which will be stored in cache
	 * @return object
	 */
	public function strip_unnecessary_merge_vars_properties( $merge_var ) {
		$array = array(
			'name' => $merge_var->name,
			'field_type' => $merge_var->field_type,
			'req' => $merge_var->req,
			'tag' => $merge_var->tag
		);

		if ( isset( $merge_var->choices ) ) {
			$array["choices"] = $merge_var->choices;
		}

		return (object) $array;

	}

} 