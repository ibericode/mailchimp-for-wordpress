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

}