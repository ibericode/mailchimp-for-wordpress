<?php

class MC4WP_Field_Mapper {

	/**
	 * @var array
	 */
	protected $form_data = array();

	/**
	 * @var array
	 */
	protected $lists = array();

	/**
	 * @var MC4WP_MailChimp
	 */
	protected $mailchimp;

	/**
	 * @var array|bool
	 */
	protected $list_fields_map;

	/**
	 * @var array
	 */
	protected $global_fields = array();

	/**
	 * @var array
	 */
	protected $mapped_fields = array( 'EMAIL' );

	/**
	 * @var array
	 */
	protected $unmapped_fields = array();

	/**
	 * @var bool
	 */
	public $success = false;

	/**
	 * @var string
	 */
	protected $error_code = '';

	/**
	 * @param array $form_data
	 * @param array $lists
	 */
	public function __construct( array $form_data, array $lists ) {
		$this->form_data = $form_data;
		$this->lists = $lists;

		$this->mailchimp = new MC4WP_MailChimp();

		$this->list_fields_map = $this->map_lists_fields();

		// only proceed if successful
		if( $this->list_fields_map ) {
			$this->success = true;
			$this->global_fields = $this->map_global_fields();
			$this->unmapped_fields = $this->find_unmapped_fields();
		}
	}

	public function get_list_fields_map() {
		return $this->list_fields_map;
	}

	public function get_global_fields() {
		return $this->global_fields;
	}

	public function get_mapped_fields() {
		return $this->mapped_fields;
	}

	public function get_unmapped_fields() {
		return $this->unmapped_fields;
	}

	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * @return array|bool
	 */
	public function map_lists_fields() {

		$map = array();

		// loop through selected lists
		foreach( $this->lists as $list_id ) {

			$list = $this->mailchimp->get_list( $list_id, false, true );

			// skip this list if it's unexisting
			if( ! is_object( $list ) || ! isset( $list->merge_vars ) ) {
				continue;
			}

			// generate map for this given list
			$list_map = $this->map_data_to_list( $list );
			if( $list_map === false ) {
				return false;
			}

			$map[ $list_id ] = $list_map;
		}

		return $map;
	}

	/**
	 * @return array
	 */
	public function find_unmapped_fields() {

		$unmapped_fields = array();

		// is there still unmapped data left?
		$total_fields_mapped = count( $this->mapped_fields ) + count( $this->global_fields );

		if( $total_fields_mapped < count( $this->form_data ) ) {
			foreach( $this->form_data as $field_key => $field_value ) {

				if( $this->is_internal_var( $field_key ) ) {
					continue;
				}

				if( ! in_array( $field_key, $this->mapped_fields ) ) {
					$unmapped_fields[ $field_key ] = $field_value;
				}
			}
		}

		return $unmapped_fields;
	}

	/**
	 * @return array
	 */
	private function map_global_fields() {
		$global_fields = array();

		// map global fields
		$global_field_names = array( 'MC_LOCATION', 'MC_NOTES', 'MC_LANGUAGE' );
		foreach( $global_field_names as $field_name ) {
			if( isset( $this->form_data[ $field_name ] ) ) {
				$global_fields[ $field_name ] = $this->form_data[ $field_name ];
			}
		}

		return $global_fields;
	}

	/**
	 * @param $list
	 *
	 * @return array
	 */
	private function map_data_to_list( $list ) {

		// start with empty list map
		$list_map = array();

		// loop through other list fields
		foreach( $list->merge_vars as $field ) {

			// skip EMAIL field
			if( $field->tag === 'EMAIL' ) {
				continue;
			}

			// check if field is required
			if( $field->req ) {
				if( ! isset( $this->form_data[ $field->tag ] ) || '' === $this->form_data[ $field->tag ] ) {
					$this->error_code = 'required_field_missing';
					return false;
				}
			}

			// if field is not set, continue.
			if( ! isset( $this->form_data[ $field->tag ] ) ) {
				continue;
			}

			// grab field value from data
			$field_value = $this->form_data[ $field->tag ];

			// format field value according to its type
			$field_value = $this->format_field_value( $field_value, $field->field_type );

			// add field value to map
			$list_map[ $field->tag ] = $field_value;
		}

		// loop through list groupings if GROUPINGS data was sent
		if( isset( $data['GROUPINGS'] ) && is_array( $data['GROUPINGS'] ) && ! empty( $list->interest_groupings ) ) {

			$list_map['GROUPINGS'] = array();

			foreach( $list->interest_groupings as $grouping ) {

				// check if data for this group was sent
				if( isset( $this->form_data['GROUPINGS'][$grouping->id] ) ) {
					$group_data = $this->form_data['GROUPINGS'][$grouping->id];
				} elseif( isset( $this->form_data['GROUPINGS'][$grouping->name] ) ) {
					$group_data = $this->form_data['GROUPINGS'][$grouping->name];
				} else {
					// no data for this grouping was sent, just continue.
					continue;
				}

				// format new grouping
				$grouping = array(
					'id' => $grouping->id,
					'groups' => $group_data,
				);

				// make sure groups is an array
				if( ! is_array( $grouping['groups'] ) ) {
					$grouping['groups'] = sanitize_text_field( $grouping['groups'] );
					$grouping['groups'] = explode( ',', $grouping['groups'] );
				}

				$list_map['GROUPINGS'][] = $grouping;
			}

			// unset GROUPINGS if no grouping data was found for this list
			if( 0 === count( $list_map['GROUPINGS'] ) ) {
				unset( $list_map['GROUPINGS'] );
			}
		}

		// add to total map
		return $list_map;
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

			// birthday fields need to be MM/DD for the MailChimp API
			case 'birthday':
				$field_value = (string) date( 'm/d', strtotime( $field_value ) );
				break;

			case 'address':

				// auto-format if addr1 is not set
				if( ! isset( $field_value['addr1'] ) ) {

					// addr1, addr2, city, state, zip, country
					$address_pieces = explode( ',', $field_value );

					// try to fill it.... this is a long shot
					$field_value = array(
						'addr1' => $address_pieces[0],
						'city'  => ( isset( $address_pieces[1] ) ) ?   $address_pieces[1] : '',
						'state' => ( isset( $address_pieces[2] ) ) ?   $address_pieces[2] : '',
						'zip'   => ( isset( $address_pieces[3] ) ) ?   $address_pieces[3] : ''
					);

				}

				break;
		}

		/**
		 * @filter `mc4wp_format_field_value`
		 * @param mixed $field_value
		 * @param string $field_type
		 * @expects mixed
		 *
		 *          Format a field value according to its MailChimp field type
		 */
		$field_value = apply_filters( 'mc4wp_format_field_value', $field_value, $field_type );

		return $field_value;
	}

	/**
	 * @param $var
	 *
	 * @return bool
	 */
	protected function is_internal_var( $var ) {

		if( $var[0] === '_' ) {
			return true;
		}

		// Ignore those fields, we don't need them
		$ignored_vars = array( 'CPTCH_NUMBER', 'CNTCTFRM_CONTACT_ACTION', 'CPTCH_RESULT', 'CPTCH_TIME' );
		if( in_array( $var, $ignored_vars ) ) {
			return true;
		}

		return false;
	}

}