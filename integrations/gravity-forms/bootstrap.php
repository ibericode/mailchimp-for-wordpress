<?php

defined( 'ABSPATH' ) or exit;

mc4wp_register_integration( 'gravity-forms', 'MC4WP_Gravity_Forms_Integration', true );

if ( class_exists( 'GFForms' ) ) {
    GF_Fields::register( new MC4WP_Gravity_Forms_Field() );

    // TODO: Render MailChimp list options here. Possibly other settings too.
    add_action( 'gform_field_standard_settings', function( $pos, $form_id ) {
        if( $pos !== 0 ) { return; }
        ?>
        <li class="mailchimp_list_setting field_setting">
            <label for="field_label" class="section_label">
                <?php esc_html_e( 'MailChimp List', 'gravityforms' ); ?>
            </label>
            <select><option>Here.</option></select>
        </li>
        <?php
    }, 10, 2);
}

// TODO
// add_action( 'gform_after_submission', 'after_submission', 10, 2 );