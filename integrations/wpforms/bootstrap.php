<?php

pl4wp_register_integration( 'wpforms', 'PL4WP_WPForms_Integration', true );

function _pl4wp_wpforms_register_field() {
    if( ! class_exists( 'WPForms_Field' ) ) {
        return;
    }

    new PL4WP_WPForms_Field();
}

add_action( 'init', '_pl4wp_wpforms_register_field' );
