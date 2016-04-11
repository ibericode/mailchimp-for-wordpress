<?php

/**
 * Class MC4WP_Field_Map
 *
 * @access private
 * @since 2.0
 * @ignore
 *
 * TODO: Write upgrade routine for new "INTERESTS" key
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
	 * @var MC4WP_Field_Formatter
	 */
	private $formatter;

	/**
	 * @param array $raw_data
	 * @param array $list_ids
	 */
	public function __construct( array $raw_data, array $list_ids ) {

		$this->formatter = new MC4WP_Field_Formatter();
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
			'INTERESTS' => array(),
		);

		// extract values for merge_vars & interest categories
		array_walk( $list->merge_fields, array( $this, 'extract_merge_field' ), $list );
		array_walk( $list->interest_categories, array( $this, 'extract_interest_category' ), $list );

		// filter out empty values
		$this->list_fields[ $list->id ] = array_filter( $this->list_fields[ $list->id ] );

		// if we have values at this point, add global fields
		if( ! empty( $this->list_fields[ $list->id ] ) ) {
			// add global fields (fields belong to ALL lists automatically)
			$this->list_fields[ $list->id ] = array_merge( $this->list_fields[ $list->id ], $this->global_fields );
		}

	}

	/**
	 * @param MC4WP_MailChimp_Merge_Field $merge_field
	 * @param int $index
	 * @param MC4WP_MailChimp_List $list
	 *
	 * @return mixed
	 */
	protected function extract_merge_field( MC4WP_MailChimp_Merge_Field $merge_field, $index, MC4WP_MailChimp_List $list ) {

		// if field is not set, continue.
		// don't use empty here as empty fields are perfectly valid (for non-required fields)
		if( ! isset( $this->raw_data[ $merge_field->tag ] ) ) {
			return;
		}

		// grab field value from data
		$value = $this->raw_data[ $merge_field->tag ];
		unset( $this->custom_fields[ $merge_field->tag ] );

		// format field value according to its type
		$value = $this->format_merge_field_value( $value, $merge_field->field_type );

		// store
		$this->list_fields[ $list->id ][ $merge_field->tag ] = $value;
		$this->formatted_data[ $merge_field->tag ] = $value;
		$this->pretty_data[ $merge_field->name ] = $value;
	}

	/**
	 * @param MC4WP_MailChimp_Interest_Category $interest_category
	 * @param string $index
	 * @param MC4WP_MailChimp_List $list
	 *
	 * @return array|null
	 */
	protected function extract_interest_category( MC4WP_MailChimp_Interest_Category $interest_category, $index, MC4WP_MailChimp_List $list ) {

		// check if data for this group was sent
		if( ! empty( $this->raw_data['INTERESTS'][$interest_category->id] ) ) {
			$groups = $this->raw_data['INTERESTS'][$interest_category->id];
		} elseif( ! empty( $this->raw_data['INTERESTS'][$interest_category->name] ) ) {
			$groups = $this->raw_data['INTERESTS'][$interest_category->name];
		} else {
			return;
		}


		// reset entire groupings array here
		unset( $this->custom_fields['INTERESTS'] );

		// make sure groups is an array
		if( ! is_array( $groups ) ) {
			$groups = array_map( 'trim', explode( ',', $groups ) );
		}

		foreach( $groups as $interest_id ) {

			// since $interest_id might be a name instead of an ID, look in interests' values
			if( ! isset( $interest_category->interests[ $interest_id ] ) ) {
				$interest_id = array_search( $interest_id, $interest_category->interests );

				if( ! $interest_id ) {
					continue;
				}
			}

			$this->list_fields[ $list->id ]['INTERESTS'][ $interest_id ] = true;
		}

		// TODO: Fix this too
		//$this->formatted_data['GROUPINGS'][ $interest_category->id ] = $interests;
		//$this->pretty_data[ $interest_category->name ] = $interests;
	}


	/**
	 * @return array
	 */
	protected function extract_global_fields() {

		// TODO: These fields are handled differently now. Act accordingly.

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
	protected function format_merge_field_value( $field_value, $field_type ) {

		$field_type = strtolower( $field_type );

		if( method_exists( $this->formatter, $field_type ) ) {
			$field_value = call_user_func( array( $this->formatter, $field_type ), $field_value );
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