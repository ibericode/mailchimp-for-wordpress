<?php

/**
 * Class MC4WP_Form_Message
 *
 * @ignore
 * @access private
 */
class MC4WP_Form_Message {

	/**
	 * @var string
	 */
	public $type = 'error';

	/**
	 * @var
	 */
	public $text;

	/**
	 * @param string $text
	 * @param string $type
	 */
	public function __construct( $text, $type = 'error' ) {
		$this->text = $text;

		if( ! empty( $type ) ) {
			$this->type = $type;
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->text;
	}

}