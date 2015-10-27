<?php

/**
 * Class MC4wP_Form_Field_Choice
 *
 * @todo move to client-side
 */
class MC4wP_Form_Field_Choice {

	/**
	 * @var
	 */
	public $value;

	/**
	 * @var string
	 */
	public $label;

	/**
	 * @var bool
	 */
	public $selected;

	/**
	 * @param        $value
	 * @param string $label
	 * @param bool   $selected
	 */
	public function __construct( $value, $label = '', $selected = false ) {
		$this->value = $value;

		if( empty( $label ) ) {
			$this->label = $value;
		} else {
			$this->label = $label;
		}

		$this->selected = $selected;
	}
}