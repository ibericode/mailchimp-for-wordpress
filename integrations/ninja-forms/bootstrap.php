<?php

mc4wp_register_integration( 'ninja-forms', 'MC4WP_Ninja_Forms_Integration', true );

if( class_exists( 'Ninja_Forms' ) ) {
    $ninja_forms = Ninja_Forms::instance();
    $ninja_forms->fields['mc4wp_optin'] = new MC4WP_Ninja_Forms_Field();
    $ninja_forms->actions['mc4wp_subscribe'] = new MC4WP_Ninja_Forms_Action();
}
