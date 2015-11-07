<?php

/**
 * Class MC4WP_Form_Message
 *
 * @api
 */
class MC4WP_Form_Message {

	public $form_id;
	public $key;
	public $default;
	public $type;

	/**
	 * @param int $form_id
	 * @param string $key
	 * @param string $default
	 * @param string $type
	 */
	public function __construct( $form_id, $key, $default, $type = 'error' ) {
		$this->key = $key;
		$this->default = $default;
		$this->type;
	}

	public function get_text() {

		$text = get_post_meta( $this->form_id, $this->key, true );

		if( empty( $text ) ) {
			return $this->default;
		}

		return $text;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$html = sprintf( '<div class="mc4wp-alert mc4wp-%s"><p>%s</p></div>', esc_attr( $this->type ), $this->get_text() );
		$html = (string) apply_filters( 'mc4wp_form_message_html', $html, $this );
		return $html;
	}

}