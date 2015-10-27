<?php

/**
 * Class MC4WP_Form_Field
 * 
 * @internal
 * @todo Move to client-side
 */
class MC4WP_Form_Field {

	/**
	 * @var string
	 */
	public $label = '';

	/**
	 * @var string
	 */
	public $name =  '';

	/**
	 * @var bool
	 */
	public $required = false;

	/**
	 * @var string
	 */
	public $default_value = '';

	/**
	 * @var MC4wP_Form_Field_Choice[]
	 */
	public $choices = array();

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @param        $label
	 * @param        $name
	 * @param        $type
	 * @param bool   $required
	 * @param string $default_value
	 * @param array  $choices
	 */
	public function __construct( $label, $name, $type, $required = false, $default_value = '', $choices = array() ) {
		$this->label = $label;
		$this->name = $name;
		$this->type = $type;
		$this->required = $required;
		$this->default_value = $default_value;
		$this->choices = $choices;
	}

}