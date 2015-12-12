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
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	protected $global_fields = array();

	/**
	 * @var array
	 */
	public $lists = array();

	/**
	 * @var array|bool
	 */
	public $list_fields = array();

	/**
	 * @var array
	 */
	public $custom_fields = array();

	/**
	 * @param array $data
	 * @param array $list_ids
	 */
	public function __construct( array $data, array $list_ids ) {
		$this->data = $data;
		$this->lists = $this->fetch_lists( $list_ids );

		// 1. assume all data is custom
		$this->custom_fields = $data;

		// 2. Map global fields
		$this->global_fields = $this->map_global_fields();

		// 3. Map list-specific fields
		$this->list_fields = $this->map_lists();
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
	protected function map_lists() {
		$map = array();

		foreach( $this->lists as $list ) {
			$map[ $list->id ] = $this->map_list_fields( $list );
		}

		// filter out empty values
		$map = array_filter( $map );

		return $map;
	}

	/**
	 * @param MC4WP_MailChimp_List $list
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function map_list_fields( MC4WP_MailChimp_List $list ) {

		$map = array();

		// loop through list fields
		foreach( $list->merge_vars as $field ) {
			$value = $this->map_list_field( $field );

			if( is_null( $value ) ) {
				continue;
			}

			$map[ $field->tag ] = $value;
		}

		// loop through list interest groupings
		if( ! empty( $list->groupings ) ) {
			$map['GROUPINGS'] = array_map( array( $this, 'map_list_grouping' ), $list->groupings );
			$map['GROUPINGS'] = array_filter( $map['GROUPINGS'] );
		}

		// add global fields (fields belong to ALL lists automatically)
		$map = array_merge( $map, $this->global_fields );

		// filter out empty values
		$map = array_filter( $map );

		return $map;
	}

	/**
	 * @param $field
	 *
	 * @return mixed
	 */
	protected function map_list_field( $field ) {

		// if field is not set, continue.
		// don't use empty here as empty fields are perfectly valid (for non-required fields)
		if( ! isset( $this->data[ $field->tag ] ) ) {
			return null;
		}

		// grab field value from data
		$value = $this->data[ $field->tag ];
		unset( $this->custom_fields[ $field->tag ] );

		// format field value according to its type
		$value = $this->format_field_value( $value, $field->field_type );

		return $value;
	}

	/**
	 * @param $grouping
	 *
	 * @return array|null
	 */
	protected function map_list_grouping( $grouping ) {

		// check if data for this group was sent
		if( ! empty( $this->data['GROUPINGS'][$grouping->id] ) ) {
			$groups = $this->data['GROUPINGS'][$grouping->id];
		} elseif( ! empty( $this->data['GROUPINGS'][$grouping->name] ) ) {
			$groups = $this->data['GROUPINGS'][$grouping->name];
		} else {
			return null;
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
		return array(
			'id' => $grouping->id,
			'groups' => $groups,
		);
	}


	/**
	 * @return array
	 */
	protected function map_global_fields() {
		$global_fields = array();

		// map global fields
		$global_field_names = array(
			'MC_LOCATION',
			'MC_NOTES',
			'MC_LANGUAGE',
			'OPTIN_IP',
		);

		foreach( $global_field_names as $field_name ) {
			if( isset( $this->data[ $field_name ] ) ) {
				$global_fields[ $field_name ] = $this->data[ $field_name ];
				unset( $this->custom_fields[ $field_name ] );
			}
		}

		return $global_fields;
	}

	/**
	 * Format field value according to its type
	 *
	 * @param $field_type
	 * @param $field_value
	 *
	 * @return array|string
	 */
	protected function format_field_value( $field_value, $field_type ) {

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
					$default_address = array(
						'addr1' => '',
						'city' => '',
						'state' => '',
						'zip' => ''
					);

					$field_value = array_merge( $default_address, $field_value );
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

	/**
	 * Returns a mostly human readable array of the given data.
	 *
	 * Data is formatted according to the data MailChimp expects.
	 *
	 * @return array
	 */
	public function pretty() {

		$pretty = array(
			'_MC4WP_LISTS' => wp_list_pluck( $this->lists, 'name' ),
			'GROUPINGS' => array()
		);

		// add custom fields
		foreach( $this->custom_fields as $key => $value ) {
			$pretty[ $key ] = $value;
		}

		foreach( $this->list_fields as $list_id => $list_fields ) {
			foreach( $list_fields as $name => $value ) {
				if( $name === 'GROUPINGS' ) {
					$groupings = $value;

					foreach( $groupings as $grouping ) {
						$pretty['GROUPINGS'][ $grouping['id'] ] = $grouping['groups'];
					}

					continue;
				}

				// just add it
				$pretty[ $name ] = $value;
			}
		}



		return $pretty;
	}



}