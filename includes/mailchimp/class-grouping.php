<?php

class MC4WP_MailChimp_Grouping {

	public $id = 0;

	public $name = '';

	public $field_type = '';

	public $groups = array();

	/**
	 * @param       $id
	 * @param       $name
	 * @param       $field_type
	 * @param array $groups
	 */
	public function __construct( $id, $name, $field_type, $groups = array() ) {
		$this->id = $id;
		$this->name = $name;
		$this->field_type = $field_type;
		$this->groups = $groups;
	}

	/**
	 * @param $data
	 * @return MC4WP_MailChimp_Grouping
	 */
	public static function from_data( $data ) {

		$instance = new self( $data->id, $data->name, $data->form_field );

		foreach( $data->groups as $group ) {
			$instance->groups[] = $group->name;
		}

		return $instance;
	}
}