<?php

/**
 * Class MC4WP_Field_Map
 *
 * @access private
 * @since 4.0
 * @ignore
 */
class MC4WP_List_Data_Mapper {

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @var array
	 */
	private $list_ids = array();

	/**
	 * @param array $data
	 * @param array $list_ids
	 */
	public function __construct( array $data, array $list_ids ) {
		$this->data = array_change_key_case( $data, CASE_UPPER );
		$this->list_ids = $list_ids;
		$this->formatter = new MC4WP_Field_Formatter();

		if( ! isset( $this->data['EMAIL'] ) ) {
			throw new InvalidArgumentException( 'Data needs at least an EMAIL key.' );
		}
	}

	/**
	 * @return MC4WP_MailChimp_Subscriber[]
	 */
	public function map() {
		$mailchimp = new MC4WP_MailChimp();
		$map = array();

		foreach( $this->list_ids as $list_id ) {
			$list = $mailchimp->get_list( $list_id, true );

			if( $list instanceof MC4WP_MailChimp_List ) {
				$map[ $list_id ] = $this->map_list( $list );
			}
		}

		return $map;
	}

	/**
	 * @param MC4WP_MailChimp_List $list
	 *
	 * @return MC4WP_MailChimp_Subscriber
	 */
	protected function map_list( MC4WP_MailChimp_List $list ) {

		$member = new MC4WP_MailChimp_Subscriber();
		$member->email_address = $this->data['EMAIL'];

		// find merge fields
		foreach( $list->merge_fields as $merge_field ) {

			// skip EMAIL field as that is handled separately (see above)
			if( $merge_field->tag === 'EMAIL' ) {
				continue;
			}

			if( ! isset( $this->data[ $merge_field->tag ] ) ) {
				continue;
			}

			// format field value
			$value = $this->data[ $merge_field->tag ];
			$value = $this->format_merge_field_value( $value, $merge_field->field_type );

			// add to map
			$member->merge_fields[ $merge_field->tag ] = $value;
		}

		// find interest categories
		foreach( $list->interest_categories as $interest_category ) {
			if( ! isset( $this->data['INTERESTS'][ $interest_category->id ] ) ) {
				continue;
			}

			$interests = $this->data['INTERESTS'][ $interest_category->id ];

			// accept pipe-separated value strings, eg "Group 1|Group 2"
			if( ! is_array( $interests ) ) {
				$interests = array_map( 'trim', explode( '|', $interests ) );
			}

			foreach( $interests as $interest_id ) {
				$interest_id = (string) $interest_id;
				$member->interests[ $interest_id ] = true;
			}
		}


		return $member;
	}

	/**
	 * @param mixed $field_value
	 * @param string $field_type
	 *
	 * @return mixed
	 */
	private function format_merge_field_value( $field_value, $field_type ) {
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