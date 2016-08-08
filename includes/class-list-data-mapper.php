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
     * @var MC4WP_Field_Formatter
     */
    private $formatter;

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

		$subscriber = new MC4WP_MailChimp_Subscriber();
		$subscriber->email_address = $this->data['EMAIL'];

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
			$subscriber->merge_fields[ $merge_field->tag ] = $value;
		}

		// find interest categories
        if( ! empty( $this->data['INTERESTS'] ) ) {
            $interests_data = $this->array_flatten_and_explode( $this->data['INTERESTS'] );

            foreach( $list->interest_categories as $interest_category ) {
                foreach( $interest_category->interests as $interest_id => $interest_name ) {
                    if( in_array( $interest_id, $interests_data, false ) ) {
                        $subscriber->interests[ $interest_id ] = true;
                    }
                }
            }
        }

        // find language
        /* @see http://kb.mailchimp.com/lists/managing-subscribers/view-and-edit-subscriber-languages?utm_source=mc-api&utm_medium=docs&utm_campaign=apidocs&_ga=1.211519638.2083589671.1469697070 */
        if( ! empty( $this->data['MC_LANGUAGE'] ) ) {
            $subscriber->language = $this->formatter->language( $this->data['MC_LANGUAGE'] );
        }

		return $subscriber;
	}


    /**
     * @param array $input
     * @return array
     */
	private function array_flatten_and_explode( array $input ) {
        $output = array();

        foreach( $input as $value ) {
            if( is_array( $value ) ) {
                $output = array_merge( $output, $this->array_flatten_and_explode( $value ) );
            } else {
                $output = array_merge( $output, array_map( 'trim', explode( '|', $value ) ) );
            }
        }

        return $output;
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