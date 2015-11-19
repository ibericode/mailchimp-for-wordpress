<?php

/**
 * Class MC4WP_MailChimp_Merge_Var
 *
 * Represents a Merge Variable (AKA: field) in MailChimp
 *
 * @access public
 */
class MC4WP_MailChimp_Merge_Var {

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $field_type;

	/**
	 * @var string
	 */
	public $tag;

	/**
	 * @var bool Is this a required field for the list it belongs to?
	 */
	public $required = false;

	/**
	 * @var array
	 */
	public $choices = array();

	/**
	 * @var bool Is this field public? As in, should it show on forms?
	 */
	public $public = true;

	/**
	 * @var string Default value for the field.
	 */
	public $default = '';

	/**
	 * @param string $name
	 * @param string $field_type
	 * @param string $tag
	 * @param bool $required
	 * @param array $choices
	 */
	public function __construct( $name, $field_type, $tag, $required = false, $choices = array() ) {
		$this->name = $name;
		$this->field_type = $field_type;
		$this->tag = strtoupper( $tag );
		$this->required = $required;
		$this->choices = $choices;
	}

	/**
	 * Creates our local object from MailChimp API data.
	 *
	 * @param object $data
	 *
	 * @return MC4WP_MailChimp_Merge_Var
	 */
	public static function from_data( $data ) {

		$instance = new self( $data->name, $data->field_type, $data->tag, $data->req );

		$optional = array(
			'choices',
			'public',
			'default'
		);

		foreach( $optional as $key ) {
			if( isset( $data->$key ) ) {
				$instance->$key = $data->$key;
			}
		}

		return $instance;
	}

}