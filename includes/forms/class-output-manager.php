<?php

class MC4WP_Form_Output_Manager {

	/**
	 * @var MC4WP_Form[]
	 */
	public $printed_forms = array();

	/**
	 * @var array
	 */
	public $printed_field_types = array();

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 *
	 */
	public function add_hooks() {
		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

		// enable shortcodes in form content
		add_filter( 'mc4wp_form_content', 'do_shortcode' );

		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Registers the [mc4wp_form] shortcode
	 */
	public function register_shortcode() {
		// register shortcodes
		add_shortcode( 'mc4wp_form', array( $this, 'output_shortcode' ) );
	}

	/**
	 * @return array
	 */
	public function get_default_attributes() {
		return array(
			'id' => 0,
			'element_id' => 'mc4wp-form-' . count( $this->printed_forms )
		);
	}

	/**
	 * @param array  $attributes
	 * @param string $content
	 * @return string
	 */
	public function output_shortcode( $attributes = array(), $content = '' ) {
		$attributes = shortcode_atts(
			$this->get_default_attributes(),
			$attributes,
			'mc4wp_form'
		);

		return $this->output_form( $attributes['id'], $attributes );
	}

	/**
	 * @param int   $id
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function output_form( $id = 0, $attributes = array() ) {

		$attributes = array_merge( $this->get_default_attributes(), $attributes );

		try {
			$form = mc4wp_get_form( $id );
		} catch( Exception $e ) {

			if( current_user_can( 'manage_options' ) ) {
				return sprintf( '<strong>MailChimp for WordPress error:</strong> %s', $e->getMessage() );
			}

			return '';
		}

		$this->printed_forms[ $form->ID ] = $form;
		$this->printed_field_types += $form->get_field_types();
		$this->printed_field_types = array_unique( $this->printed_field_types );

		return $form->output( $attributes['element_id'], $attributes );
	}

}