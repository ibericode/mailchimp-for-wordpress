<?php

class MC4WP_MailChimp_List {

	/**
	 * @var string
	 */
	public $id;

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
	 */
	public function __construct( $id, $name ) {
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * Generate Field objects for the properties of this list
	 */
	public function generate_fields() {
		$this->fields = array_merge( $this->generate_merge_var_fields(), $this->generate_grouping_fields() );
		return $this->fields;
	}

	/**
	 * Translates some MailChimp fields to our own format
	 *
	 * - Separates address fields into addr1, addr2, city, state, zip & country field
	 *
	 * @return array
	 */
	protected function generate_merge_var_fields() {

		$fields = array();
		foreach( $this->merge_vars as $merge_var ) {
			$fields = array_merge( $fields, $merge_var->get_fields() );
		}
		return $fields;
	}

	/**
	 * @return array
	 */
	public function generate_grouping_fields() {
		$fields = array();
		foreach( $this->groupings as $grouping ) {
			$fields = array_merge( $fields, $grouping->get_fields() );
		}
		return $fields;
	}




}