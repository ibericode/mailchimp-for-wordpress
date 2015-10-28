<?php

class MC4WP_MailChimp_List {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var
	 */
	public $web_id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var int
	 */
	public $subscriber_count = 0;

	/**
	 * @var MC4WP_MailChimp_Merge_Var[]
	 */
	public $merge_vars = array();

	/**
	 * @var MC4WP_MailChimp_Grouping[]
	 */
	public $groupings = array();

	/**
	 * @var array
	 */
	public $fields = array();

	/**
	 * @param string $id
	 * @param string $name
	 * @param string $web_id
	 */
	public function __construct( $id, $name, $web_id = '' ) {
		$this->id = $id;
		$this->name = $name;
		$this->web_id = $web_id;
	}

	/**
	 * @param $tag
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

		foreach( $this->merge_vars as $field ) {

			if( $field->tag !== $tag ) {
				continue;
			}

			return $field->name;
		}

		return '';
	}

	/**
	 * Get the interest grouping object for a given list.
	 *
	 * @param string $grouping_id ID of the Interest Grouping
	 *
	 * @return object|null
	 */
	public function get_grouping( $grouping_id ) {

		foreach( $this->groupings as $grouping ) {

			if( $grouping->id !== $grouping_id ) {
				continue;
			}

			return $grouping;
		}

		return null;
	}


	/**
	 * Get the name of a list grouping by its ID
	 *
	 * @param $grouping_id
	 *
	 * @return string
	 */
	public function get_grouping_name( $grouping_id ) {

		$grouping = $this->get_grouping( $grouping_id );
		if( isset( $grouping->name ) ) {
			return $grouping->name;
		}

		return '';
	}

}