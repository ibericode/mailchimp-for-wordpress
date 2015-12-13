<?php

/**
 * Class MC4WP_Field_Map
 *
 * @access private
 * @since 2.0
 * @ignore
 */
class MC4WP_Field_Map {

	/**
	 * Raw array of data
	 *
	 * @var array
	 */
	protected $raw_data = array();

	/**
	 * Global fields (default list fields)
	 *
	 * @var array
	 */
	protected $global_fields = array();

	/**
	 * Array of list instances
	 *
	 * @var MC4WP_MailChimp_List[]
	 */
	protected $lists = array();

	/**
	 * Formatted array of data
	 *
	 * @var array
	 */
	public $formatted_data = array(
		'_MC4WP_LISTS' => array(),
		'GROUPINGS' => array(),
	);

	/**
	 * @var array
	 */
	public $pretty_data = array();

	/**
	 * Map of list id's with fields belonging to that list
	 *
	 * @var array
	 */
	public $list_fields = array();

	/**
	 * Array of fields not belonging to any of the given lists
	 *
	 * @var array
	 */
	public $custom_fields = array();

	/**
	 * @param array $raw_data
	 * @param array $list_ids
	 */
	public function __construct( array $raw_data, array $list_ids ) {

		$this->raw_data = $raw_data;
		$this->lists = $this->fetch_lists( $list_ids );

		// 1. Assume all given data is custom
		$this->custom_fields = $raw_data;

		// 2. Extract global fields (default list fields)
		$this->extract_global_fields();

		// 3. Extract list-specific fields
		$this->extract_list_fields();

		// 4. Add all leftover fields to data but make sure not to overwrite known fields
		$this->formatted_data = array_merge( $this->custom_fields, $this->formatted_data );
		$this->pretty_data = array_merge( $this->custom_fields, $this->pretty_data );
	}

	/**
	 * @param array $list_ids
	 * @return MC4WP_MailChimp_List[]
	 */
	protected function fetch_lists( array $list_ids ) {
		$mailchimp = new MC4WP_MailChimp();
		$lists = array();

		foreach( $list_ids as $id ) {
			$list = $mailchimp->get_list( $id, true );

			if( $list instanceof MC4WP_MailChimp_List ) {
				$lists[ $id ] = $list;
			}
		}

		return $lists;
	}

	/**
	 * @return array
	 */
	protected function extract_list_fields() {
		array_walk( $this->lists, array( $this, 'extract_fields_for_list' ) );
		$this->list_fields = array_filter( $this->list_fields );
		$this->formatted_data[ '_MC4WP_LISTS' ] = wp_list_pluck( $this->lists, 'name' );
		$this->pretty_data[ 'Lists' ] = $this->formatted_data[ '_MC4WP_LISTS' ];
	}

	/**
	 * @param MC4WP_MailChimp_List $list
	 *
	 * @return array
	 */
	protected function extract_fields_for_list( MC4WP_MailChimp_List $list ) {

		$this->list_fields[ $list->id ] = array(
			'GROUPINGS' => array(),
		);

		// extract values for merge_vars & groupings
		array_walk( $list->merge_vars, array( $this, 'extract_merge_var' ), $list );
		array_walk( $list->groupings, array( $this, 'extract_grouping' ), $list );

		// filter out empty values
		$this->list_fields[ $list->id ]['GROUPINGS'] = array_filter( $this->list_fields[ $list->id ]['GROUPINGS'] );
		$this->list_fields[ $list->id ] = array_filter( $this->list_fields[ $list->id ] );

		// if we have values at this point, add global fields
		if( ! empty( $this->list_fields[ $list->id ] ) ) {
			// add global fields (fields belong to ALL lists automatically)
			$this->list_fields[ $list->id ] = array_merge( $this->list_fields[ $list->id ], $this->global_fields );
		}

	}

	/**
	 * @param MC4WP_MailChimp_Merge_Var $merge_var
	 *
	 * @return mixed
	 */
	protected function extract_merge_var( MC4WP_MailChimp_Merge_Var $merge_var, $index, MC4WP_MailChimp_List $list ) {

		// if field is not set, continue.
		// don't use empty here as empty fields are perfectly valid (for non-required fields)
		if( ! isset( $this->raw_data[ $merge_var->tag ] ) ) {
			return;
		}

		// grab field value from data
		$value = $this->raw_data[ $merge_var->tag ];
		unset( $this->custom_fields[ $merge_var->tag ] );

		// format field value according to its type
		$value = $this->format_merge_var_value( $value, $merge_var->field_type );

		// store
		$this->list_fields[ $list->id ][ $merge_var->tag ] = $value;
		$this->formatted_data[ $merge_var->tag ] = $value;
		$this->pretty_data[ $merge_var->name ] = $value;
	}

	/**
	 * @param MC4WP_MailChimp_Grouping $grouping
	 * @param string $index
	 * @param MC4WP_MailChimp_List $list
	 *
	 * @return array|null
	 */
	protected function extract_grouping( MC4WP_MailChimp_Grouping $grouping, $index, MC4WP_MailChimp_List $list ) {

		// check if data for this group was sent
		if( ! empty( $this->raw_data['GROUPINGS'][$grouping->id] ) ) {
			$groups = $this->raw_data['GROUPINGS'][$grouping->id];
		} elseif( ! empty( $this->raw_data['GROUPINGS'][$grouping->name] ) ) {
			$groups = $this->raw_data['GROUPINGS'][$grouping->name];
		} else {
			return;
		}

		// reset entire groupings array here
		unset( $this->custom_fields['GROUPINGS'] );

		// make sure groups is an array
		if( ! is_array( $groups ) ) {
			$groups = array_map( 'trim', explode( ',', $groups ) );
		}

		// if groups is an array of id's, get the group name instead
		foreach( $groups as $key => $group_name_or_id ) {
			if( is_numeric( $group_name_or_id ) && isset( $grouping->groups[ $group_name_or_id ] ) ) {
				$groups[ $key ] = $grouping->groups[ $group_name_or_id ];
			}
		}

		// format grouping data for MailChimp
		$formatted_grouping = array(
			'id' => $grouping->id,
			'groups' => $groups,
		);

		// add to list data
		$this->list_fields[ $list->id ]['GROUPINGS'][] = $formatted_grouping;
		$this->formatted_data['GROUPINGS'][ $grouping->id ] = $groups;

		//
		$this->pretty_data[ $grouping->name ] = $groups;
	}


	/**
	 * @return array
	 */
	protected function extract_global_fields() {
		// map global fields
		$global_field_names = array(
			'MC_LOCATION',
			'MC_NOTES',
			'MC_LANGUAGE',
			'OPTIN_IP',
		);

		foreach( $global_field_names as $field_name ) {
			if( isset( $this->raw_data[ $field_name ] ) ) {

				$this->global_fields[ $field_name ] = $this->raw_data[ $field_name ];
				unset( $this->custom_fields[ $field_name ] );

				$this->formatted_data[ $field_name ] = $this->raw_data[ $field_name ];
			}
		}
	}

	/**
	 * Format field value according to its type
	 *
	 * @param $field_type
	 * @param $field_value
	 *
	 * @return array|string
	 */
	protected function format_merge_var_value( $field_value, $field_type ) {

		$field_type = strtolower( $field_type );

		switch( $field_type ) {

			case 'number':
				$field_value = floatval( $field_value );
				break;

			case 'date':
				$field_value = (string) date('Y-m-d', strtotime( $field_value ) );
				break;

			// birthday fields need to be MM/DD for the MailChimp API
			case 'birthday':
				$field_value = (string) date( 'm/d', strtotime( $field_value ) );
				break;

			case 'address':

				// auto-format if this is a string
				if( is_string( $field_value ) ) {

					// addr1, addr2, city, state, zip, country
					$address_pieces = explode( ',', $field_value );

					// try to fill it.... this is a long shot
					$field_value = array(
						'addr1' => $address_pieces[0],
						'city'  => ( isset( $address_pieces[1] ) ) ?   $address_pieces[1] : '',
						'state' => ( isset( $address_pieces[2] ) ) ?   $address_pieces[2] : '',
						'zip'   => ( isset( $address_pieces[3] ) ) ?   $address_pieces[3] : ''
					);
				} elseif( is_array( $field_value ) ) {
					// merge with array of empty defaults to allow skipping certain fields
					$default = array_fill_keys( array( 'addr1', 'city', 'state', 'zip' ), '' );
					$field_value = array_merge( $default, $field_value );
				}

				break;
		}

		/**
		 * Filters the value of a field after it is formatted.
		 *
		 * Use this to format a field value according to the field type (in MailChimp).
		 *
		 * @since 3.0
		 * @param string $field_value The value
		 * @param string $field_type The type of the field (in MailChimp)
		 */
		$field_value = apply_filters( 'mc4wp_format_field_value', $field_value, $field_type );

		return $field_value;
	}

}