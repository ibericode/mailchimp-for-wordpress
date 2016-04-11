<?php

/**
 * Class MC4WP_MailChimp_Grouping
 *
 * Represents an Interest Grouping in MailChimp.
 *
 * @access public
 */
class MC4WP_MailChimp_Grouping {

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $field_type = '';

	/**
	 * @var string[]
	 */
	public $groups = array();

	/**
	 * @param int $id
	 * @param string $name
	 * @param string $field_type
	 * @param array $groups
	 */
	public function __construct( $id, $name, $field_type, $groups = array() ) {
		$this->id = $id;
		$this->name = $name;
		$this->field_type = $field_type;
		$this->groups = $groups;
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public function get_group_name_by_id( $id ) {
		if( isset( $this->groups[ $id ] ) ) {
			return $this->groups[ $id ];
		}

		return $id;
	}

	/**
	 * @param object $data
	 *
	 * @return MC4WP_MailChimp_Grouping
	 */
	public static function from_data( $data ) {

		$instance = new self( $data->id, $data->name, $data->form_field );

		// add group names as strings
		foreach( $data->groups as $group ) {
			$instance->groups[ $group->id ] = $group->name;
		}

		return $instance;
	}

}