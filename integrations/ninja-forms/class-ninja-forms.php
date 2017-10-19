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
	    add_action( 'mc4wp_integration_ninja_forms_subscribe', array( $this, 'subscribe_from_ninja_forms' ), 10, 7 );
    }

    public function subscribe_from_ninja_forms( $email_address, $merge_fields, $list_id, $double_optin = true, $update_existing = false, $replace_interests = false, $form_id = 0 ) {
        // set options from parameters (coming from action)
        $orig_options = $this->options;
        $this->options['double_optin'] = $double_optin;
        $this->options['update_existing'] = $update_existing;
        $this->options['replace_interests'] = $replace_interests;
        $this->options['lists'] = array( $list_id );

        $data = $merge_fields;
        $data['EMAIL'] = $email_address;

        $this->subscribe( $data, $form_id );

        // revert to original options
        $this->options = $orig_options;
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
        return array();
    }

    /**
     * @param int $form_id
     * @return string
     */
    public function get_object_link( $form_id ) {
        return '<a href="' . admin_url( sprintf( 'admin.php?page=ninja-forms&form_id=%d', $form_id ) ) . '">Ninja Forms</a>';
    }

}