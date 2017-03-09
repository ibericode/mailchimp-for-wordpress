<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Ninja_Forms_Integration
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
	    $this->register_classes();

	    add_action( 'ninja_forms_after_submission', array( $this, 'after_submission' ) );
	}

	public function after_submission( $data ) {
	    //var_dump( $data ); die(); //ninja_forms_after_submission
    }

    /**
     * Adds the field to the Ninja Forms class $fields property.
     * A nicer way would be to use the Ninja Forms `ninja_forms_register_fields` filter here, but that is running on `plugins_loaded`....
     */
    public function register_classes() {
	   $ninja_forms = Ninja_Forms::instance();
	   // TODO: Add support for this field.
//       $field = new MC4WP_Ninja_Forms_Field();
//	   $ninja_forms->fields['mc4wp'] = $field;

	   $action = new MC4WP_Ninja_Forms_Action();
	   $ninja_forms->actions['mc4wp'] = $action;
    }

	/**
	 * @return bool
	 */
	public function is_installed() {
		return class_exists( 'Ninja_Forms' );
	}

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements() {
        return array_diff( parent::get_ui_elements(), array( 'enabled', 'implicit', 'precheck', 'css', 'label' ) );
    }

}