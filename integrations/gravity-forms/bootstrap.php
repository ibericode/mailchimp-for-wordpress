<?php

defined( 'ABSPATH' ) or exit;

pl4wp_register_integration( 'gravity-forms', 'PL4WP_Gravity_Forms_Integration', true );

if ( class_exists( 'GF_Fields' ) ) {
    GF_Fields::register( new PL4WP_Gravity_Forms_Field() );
}
