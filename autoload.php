<?php

require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/deprecated-functions.php';
require __DIR__ . '/includes/forms/functions.php';
require __DIR__ . '/includes/forms/admin-functions.php';
require __DIR__ . '/includes/integrations/functions.php';

spl_autoload_register(function ($class) {
    static $classmap = [
        'MC4WP_API_Connection_Exception' => '/includes/api/class-connection-exception.php',
        'MC4WP_API_Exception' => '/includes/api/class-exception.php',
        'MC4WP_API_Resource_Not_Found_Exception' => '/includes/api/class-resource-not-found-exception.php',
        'MC4WP_API_V3' => '/includes/api/class-api-v3.php',
        'MC4WP_API_V3_Client' => '/includes/api/class-api-v3-client.php',
        'MC4WP_Admin' => '/includes/admin/class-admin.php',
        'MC4WP_Admin_Ads' => '/includes/admin/class-ads.php',
        'MC4WP_Admin_Ajax' => '/includes/admin/class-admin-ajax.php',
        'MC4WP_Admin_Messages' => '/includes/admin/class-admin-messages.php',
        'MC4WP_Admin_Review_Notice' => '/includes/admin/class-review-notice.php',
        'MC4WP_Admin_Texts' => '/includes/admin/class-admin-texts.php',
        'MC4WP_Admin_Tools' => '/includes/admin/class-admin-tools.php',
        'MC4WP_AffiliateWP_Integration' => '/integrations/affiliatewp/class-affiliatewp.php',
        'MC4WP_BuddyPress_Integration' => '/integrations/buddypress/class-buddypress.php',
        'MC4WP_Comment_Form_Integration' => '/integrations/wp-comment-form/class-comment-form.php',
        'MC4WP_Contact_Form_7_Integration' => '/integrations/contact-form-7/class-contact-form-7.php',
        'MC4WP_Container' => '/includes/class-container.php',
        'MC4WP_Custom_Integration' => '/integrations/custom/class-custom.php',
        'MC4WP_Debug_Log' => '/includes/class-debug-log.php',
        'MC4WP_Debug_Log_Reader' => '/includes/class-debug-log-reader.php',
        'MC4WP_Dynamic_Content_Tags' => '/includes/class-dynamic-content-tags.php',
        'MC4WP_Easy_Digital_Downloads_Integration' => '/integrations/easy-digital-downloads/class-easy-digital-downloads.php',
        'MC4WP_Events_Manager_Integration' => '/integrations/events-manager/class-events-manager.php',
        'MC4WP_Field_Formatter' => '/includes/class-field-formatter.php',
        'MC4WP_Field_Guesser' => '/includes/class-field-guesser.php',
        'MC4WP_Form' => '/includes/forms/class-form.php',
        'MC4WP_Form_AMP' => '/includes/forms/class-form-amp.php',
        'MC4WP_Form_Asset_Manager' => '/includes/forms/class-asset-manager.php',
        'MC4WP_Form_Element' => '/includes/forms/class-form-element.php',
        'MC4WP_Form_Listener' => '/includes/forms/class-form-listener.php',
        'MC4WP_Form_Manager' => '/includes/forms/class-form-manager.php',
        'MC4WP_Form_Notice' => '/includes/forms/class-form-message.php',
        'MC4WP_Form_Output_Manager' => '/includes/forms/class-output-manager.php',
        'MC4WP_Form_Previewer' => '/includes/forms/class-form-previewer.php',
        'MC4WP_Form_Tags' => '/includes/forms/class-form-tags.php',
        'MC4WP_Form_Widget' => '/includes/forms/class-widget.php',
        'MC4WP_Forms_Admin' => '/includes/forms/class-admin.php',
        'MC4WP_Give_Integration' => '/integrations/give/class-give.php',
        'MC4WP_Gravity_Forms_Field' => '/integrations/gravity-forms/class-field.php',
        'MC4WP_Gravity_Forms_Integration' => '/integrations/gravity-forms/class-gravity-forms.php',
        'MC4WP_Integration' => '/includes/integrations/class-integration.php',
        'MC4WP_Integration_Admin' => '/includes/integrations/class-admin.php',
        'MC4WP_Integration_Fixture' => '/includes/integrations/class-integration-fixture.php',
        'MC4WP_Integration_Manager' => '/includes/integrations/class-integration-manager.php',
        'MC4WP_Integration_Tags' => '/includes/integrations/class-integration-tags.php',
        'MC4WP_List_Data_Mapper' => '/includes/class-list-data-mapper.php',
        'MC4WP_MailChimp' => '/includes/class-mailchimp.php',
        'MC4WP_MailChimp_Subscriber' => '/includes/class-mailchimp-subscriber.php',
        'MC4WP_MemberPress_Integration' => '/integrations/memberpress/class-memberpress.php',
        'MC4WP_Ninja_Forms_Action' => '/integrations/ninja-forms/class-action.php',
        'MC4WP_Ninja_Forms_Field' => '/integrations/ninja-forms/class-field.php',
        'MC4WP_Ninja_Forms_Integration' => '/integrations/ninja-forms/class-ninja-forms.php',
        'MC4WP_Ninja_Forms_V2_Integration' => '/integrations/ninja-forms-2/class-ninja-forms.php',
        'MC4WP_Plugin' => '/includes/class-plugin.php',
        'MC4WP_Procaptcha_Integration' => '/integrations/prosopo-procaptcha/class-procaptcha-integration.php',
        'MC4WP_Procaptcha' => '/integrations/prosopo-procaptcha/class-procaptcha.php',
        'MC4WP_Queue' => '/includes/class-queue.php',
        'MC4WP_Queue_Job' => '/includes/class-queue-job.php',
        'MC4WP_Registration_Form_Integration' => '/integrations/wp-registration-form/class-registration-form.php',
        'MC4WP_Tools' => '/includes/class-tools.php',
        'MC4WP_Upgrade_Routines' => '/includes/admin/class-upgrade-routines.php',
        'MC4WP_User_Integration' => '/includes/integrations/class-user-integration.php',
        'MC4WP_WPForms_Field' => '/integrations/wpforms/class-field.php',
        'MC4WP_WPForms_Integration' => '/integrations/wpforms/class-wpforms.php',
        'MC4WP_WooCommerce_Integration' => '/integrations/woocommerce/class-woocommerce.php',
    ];

    if (isset($classmap[$class])) {
        require __DIR__ . $classmap[$class];
    }
});
