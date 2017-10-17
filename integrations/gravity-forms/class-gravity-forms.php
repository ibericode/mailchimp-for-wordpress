<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Ninja_Forms_Integration
 *
 * @ignore
 */
class MC4WP_Gravity_Forms_Integration extends MC4WP_Integration {

    /**
     * @var string
     */
    public $name = "Gravity Forms";

    /**
     * @var string
     */
    public $description = "Subscribe visitors from your Gravity Forms forms.";


    /**
     * Add hooks
     */
    public function add_hooks() {}

    /**
     * @return bool
     */
    public function is_installed() {
        return class_exists( 'GF_Field' );
    }

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements() {
        return array();
    }

}