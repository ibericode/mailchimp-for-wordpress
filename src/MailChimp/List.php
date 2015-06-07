<?php

class MC4WP_MailChimp_List {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var stdClass
	 */
	public $name = '';

	/**
	 * @var array
	 */
	public $merge_vars = array();

	/**
	 * @var array
	 */
	public $interest_groupings = array();

	/**
	 * @var int
	 */
	public $subscriber_count = 0;

	/**
	 * @param string $list_id
	 * @param stdClass $data
	 */
	public function __construct( $list_id, stdClass $data = null ) {
		$this->id = $list_id;

		if( $data ) {
			$this->name = $data->name;
			$this->merge_vars = $data->merge_vars;
			$this->interest_groupings = $data->interest_groupings;
			$this->subscriber_count = $data->subscriber_count;
		}
	}

	/**
	 * @param string $tag
	 *
	 * @return string
	 */
	public function get_field_type_by_tag( $tag ) {

		foreach( $this->merge_vars as $field ) {
			if( $field->tag === $tag ) {
				return $field->field_type;
			}
		}

		return 'text';
	}

	/**
	 * @param string $tag
	 *
	 * @return string
	 */
	public function get_field_name_by_tag( $tag ) {

		// try default fields
		switch( $tag ) {
			case 'EMAIL':
				return __( 'Email address', 'mailchimp-for-wp' );
				break;


			case 'OPTIN_IP':
				return __( 'IP Address', 'mailchimp-for-wp' );
				break;
		}

		// try list merge vars first
		foreach( $this->merge_vars as $field ) {

			if( $field->tag !== $tag ) {
				continue;
			}

			return $field->name;
		}

		// field unknown, fall back to field tag
		return $tag;
	}

	/**
	 * @param $grouping_id
	 *
	 * @return stdClass|null
	 */
	public function get_interest_grouping( $grouping_id ) {
		foreach( $this->interest_groupings as $grouping ) {

			if( $grouping->id !== $grouping_id ) {
				continue;
			}

			return $grouping;
		}

		return null;
	}

	/**
	 * @param int $grouping_id
	 *
	 * @return string
	 */
	public function get_interest_grouping_name( $grouping_id ) {
		$grouping = $this->get_interest_grouping( $grouping_id );
		if( is_object( $grouping ) && isset( $grouping->groups ) ) {
			return $grouping->name;
		}

		return '';
	}

	/**
	 * @param string $grouping_id
	 * @param int|string $group_id_or_name
	 *
	 * @return stdClass|null
	 */
	public function get_interest_grouping_group( $grouping_id, $group_id_or_name ) {
		$grouping = $this->get_interest_grouping( $grouping_id );

		if( is_object( $grouping ) && isset( $grouping->groups ) ) {
			foreach( $grouping->groups as $group ) {

				if( $group->id == $group_id_or_name || $group->name === $group_id_or_name ) {
					return $group;
				}

			}
		}

		return null;
	}

	/**
	 * @param string $list_id
	 *
	 * @return MC4WP_MailChimp_List
	 */
	public static function make( $list_id ) {
		return MC4WP_MailChimp_Tools::get_list( $list_id, false, true );
	}

}