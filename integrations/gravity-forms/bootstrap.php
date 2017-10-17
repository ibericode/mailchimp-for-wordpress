<?php

defined( 'ABSPATH' ) or exit;

mc4wp_register_integration( 'gravity-forms', 'MC4WP_Gravity_Forms_Integration', true );

if ( class_exists( 'GFForms' ) ) {
    GF_Fields::register( new MC4WP_Gravity_Forms_Field() );

    // TODO: Render MailChimp list options here. Possibly other settings too.
    add_action( 'gform_field_standard_settings', function( $pos, $form_id ) {
        if( $pos !== 0 ) { return; }
        $mailchimp = new MC4WP_MailChimp();
        $lists = $mailchimp->get_cached_lists();
        ?>
        <li class="mailchimp_list_setting field_setting">
            <label for="field_mailchimp_list" class="section_label">
                <?php esc_html_e( 'MailChimp list', 'mailchimp-for-wp' ); ?>
            </label>
            <select id="field_mailchimp_list" onchange="SetFieldProperty('mailchimp_list', this.value)">
                <option value="" disabled><?php _e( 'Select a MailChimp list', 'mailchimp-for-wp' ); ?></option>
                <?php foreach( $lists as $list ) {
                    echo sprintf( '<option value="%s">%s</option>', $list->id, $list->name );
                } ?>
            </select>
        </li>
        <li class="mailchimp_double_optin field_setting">
            <label for="field_mailchimp_double_optin" class="section_label">
                <?php esc_html_e( 'Double opt-in?', 'mailchimp-for-wp' ); ?>
            </label>
            <select id="field_mailchimp_double_optin" onchange="SetFieldProperty('mailchimp_double_optin', this.value)">
                <option value="1"><?php echo __( 'Yes' ); ?></option>
                <option value="0"><?php echo __( 'No' ); ?></option>
            </select>
        </li>
        <?php
    }, 10, 2);

    add_action( 'gform_editor_js', function() {
        ?>

        <script type="text/javascript">
             /*
             * When the field settings are initialized, populate
             * the custom field setting.
             */
            jQuery(document).on('gform_load_field_settings', function(ev, field) {
                jQuery('#field_mailchimp_list').val(field.mailchimp_list || '');
                jQuery('#field_mailchimp_double_optin').val(field.mailchimp_double_optin || "1");
            });
        </script>

        <?php
    } );

}

// TODO
// add_action( 'gform_after_submission', 'after_submission', 10, 2 );