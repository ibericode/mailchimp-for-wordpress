<?php

defined( 'ABSPATH' ) or exit;

mc4wp_register_integration( 'gravity-forms', 'MC4WP_Gravity_Forms_Integration', true );

if ( class_exists( 'GF_Fields' ) ) {
	GF_Fields::register( new MC4WP_Gravity_Forms_Field() );
}
