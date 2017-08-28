<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_WPForms_Integration
 *
 * @ignore
 */
class MC4WP_WPForms_Integration extends MC4WP_Integration {

    /**
     * @var string
     */
    public $name = "WPForms";

    /**
     * @var string
     */
    public $description = "Subscribe visitors from your WPForms forms.";


    /**
     * Add hooks
     */
    public function add_hooks() {}

    /**
     * @return bool
     */
    public function is_installed() {
        return class_exists( 'WPForms' );
    }

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements() {
        return array();
    }

}