<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Events_Manager_Integration
 *
 * @ignore
 */
class MC4WP_Ninja_Forms_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	public $name = "Ninja Forms";

	/**
	 * @var string
	 */
	public $description = "Subscribe visitors from your Ninja Forms forms.";


	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'register_field' ) );
	}

	public function register_field() {
		$args = array(
			'name' => __( 'MailChimp', 'ninja-forms' ),
			'edit_function' => '',
			'display_function' => 'ninja_forms_field_checkbox_display',
			'group' => 'standard_fields',
			'sidebar' => 'template_fields',
			'edit_placeholder' => false,
            'edit_meta' => true,
            'edit_options' => '',
            'post_process' => array( $this, 'process' ),
            'default_label' => $this->options['label']

		);

		ninja_forms_register_field( 'mc4wp-subscribe', $args );
	}

    /**
	 * Process form submissions
	 *
     * @param int $id
     * @param string $value
     *
	 * @return bool|string
	 */
	public function process( $id, $value ) {

	    // field was not checked
        if( $value !== 'checked' ) {
            return false;
        }

		/**
		 * @var Ninja_Forms_Processing $ninja_forms_processing
		 */
		global $ninja_forms_processing;

		// generate an array of field label => field value
		$fields = $ninja_forms_processing->get_all_submitted_fields();

		$pretty = array();
		foreach( $fields as $field_id => $field_value ) {

			// try admin label for "mc4wp-" prefixed fields, otherwise use general label
			$label = $ninja_forms_processing->get_field_setting( $field_id, 'admin_label' );
			if( empty( $label ) || stripos( $label, 'mc4wp-' ) !== 0 ) {
				$label = $ninja_forms_processing->get_field_setting( $field_id, 'label' );
			}

			$pretty[ $label ] = $field_value;
		}

		// guess mailchimp variables
		$parser = new MC4WP_Field_Guesser( $pretty );
		$data = $parser->combine( array( 'guessed', 'namespaced' ) );

		// do nothing if no email was found
		if( empty( $data['EMAIL'] ) ) {
			return false;
		}

		return $this->subscribe( $data, $ninja_forms_processing->get_form_ID() );
	}


	/**
	 * @return bool
	 */
	public function is_installed() {
		return function_exists( 'ninja_forms_register_field' );
	}

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements() {
        return array_diff( parent::get_ui_elements(), array( 'enabled', 'implicit', 'precheck', 'css', 'label' ) );
    }

}