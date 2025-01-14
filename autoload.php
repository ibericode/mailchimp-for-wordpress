<?php

require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/deprecated-functions.php';
require __DIR__ . '/includes/forms/functions.php';
require __DIR__ . '/includes/forms/admin-functions.php';
require __DIR__ . '/includes/integrations/functions.php';

// require API class manually because our classloader is case-sensitive
require __DIR__ . '/includes/api/class-api-v3.php';

// load other classes dynamically
spl_autoload_register(function ($class) {
    static $classmap = [
        'MC4WP_API_Connection_Exception' => __DIR__ . '/includes/api/class-connection-exception.php',
        'MC4WP_API_Exception' => __DIR__ . '/includes/api/class-exception.php',
        'MC4WP_API_Resource_Not_Found_Exception' => __DIR__ . '/includes/api/class-resource-not-found-exception.php',
        'MC4WP_API_V3' => __DIR__ . '/includes/api/class-api-v3.php',
        'MC4WP_API_V3_Client' => __DIR__ . '/includes/api/class-api-v3-client.php',
        'MC4WP_Admin' => __DIR__ . '/includes/admin/class-admin.php',
        'MC4WP_Admin_Ads' => __DIR__ . '/includes/admin/class-ads.php',
        'MC4WP_Admin_Ajax' => __DIR__ . '/includes/admin/class-admin-ajax.php',
        'MC4WP_Admin_Messages' => __DIR__ . '/includes/admin/class-admin-messages.php',
        'MC4WP_Admin_Review_Notice' => __DIR__ . '/includes/admin/class-review-notice.php',
        'MC4WP_Admin_Texts' => __DIR__ . '/includes/admin/class-admin-texts.php',
        'MC4WP_Admin_Tools' => __DIR__ . '/includes/admin/class-admin-tools.php',
        'MC4WP_AffiliateWP_Integration' => __DIR__ . '/integrations/affiliatewp/class-affiliatewp.php',
        'MC4WP_BuddyPress_Integration' => __DIR__ . '/integrations/buddypress/class-buddypress.php',
        'MC4WP_Comment_Form_Integration' => __DIR__ . '/integrations/wp-comment-form/class-comment-form.php',
        'MC4WP_Contact_Form_7_Integration' => __DIR__ . '/integrations/contact-form-7/class-contact-form-7.php',
        'MC4WP_Container' => __DIR__ . '/includes/class-container.php',
        'MC4WP_Custom_Integration' => __DIR__ . '/integrations/custom/class-custom.php',
        'MC4WP_Debug_Log' => __DIR__ . '/includes/class-debug-log.php',
        'MC4WP_Debug_Log_Reader' => __DIR__ . '/includes/class-debug-log-reader.php',
        'MC4WP_Dynamic_Content_Tags' => __DIR__ . '/includes/class-dynamic-content-tags.php',
        'MC4WP_Easy_Digital_Downloads_Integration' => __DIR__ . '/integrations/easy-digital-downloads/class-easy-digital-downloads.php',
        'MC4WP_Events_Manager_Integration' => __DIR__ . '/integrations/events-manager/class-events-manager.php',
        'MC4WP_Field_Formatter' => __DIR__ . '/includes/class-field-formatter.php',
        'MC4WP_Field_Guesser' => __DIR__ . '/includes/class-field-guesser.php',
        'MC4WP_Form' => __DIR__ . '/includes/forms/class-form.php',
        'MC4WP_Form_AMP' => __DIR__ . '/includes/forms/class-form-amp.php',
        'MC4WP_Form_Asset_Manager' => __DIR__ . '/includes/forms/class-asset-manager.php',
        'MC4WP_Form_Element' => __DIR__ . '/includes/forms/class-form-element.php',
        'MC4WP_Form_Listener' => __DIR__ . '/includes/forms/class-form-listener.php',
        'MC4WP_Form_Manager' => __DIR__ . '/includes/forms/class-form-manager.php',
        'MC4WP_Form_Notice' => __DIR__ . '/includes/forms/class-form-message.php',
        'MC4WP_Form_Output_Manager' => __DIR__ . '/includes/forms/class-output-manager.php',
        'MC4WP_Form_Previewer' => __DIR__ . '/includes/forms/class-form-previewer.php',
        'MC4WP_Form_Tags' => __DIR__ . '/includes/forms/class-form-tags.php',
        'MC4WP_Form_Widget' => __DIR__ . '/includes/forms/class-widget.php',
        'MC4WP_Forms_Admin' => __DIR__ . '/includes/forms/class-admin.php',
        'MC4WP_Give_Integration' => __DIR__ . '/integrations/give/class-give.php',
        'MC4WP_Gravity_Forms_Field' => __DIR__ . '/integrations/gravity-forms/class-field.php',
        'MC4WP_Gravity_Forms_Integration' => __DIR__ . '/integrations/gravity-forms/class-gravity-forms.php',
        'MC4WP_Integration' => __DIR__ . '/includes/integrations/class-integration.php',
        'MC4WP_Integration_Admin' => __DIR__ . '/includes/integrations/class-admin.php',
        'MC4WP_Integration_Fixture' => __DIR__ . '/includes/integrations/class-integration-fixture.php',
        'MC4WP_Integration_Manager' => __DIR__ . '/includes/integrations/class-integration-manager.php',
        'MC4WP_Integration_Tags' => __DIR__ . '/includes/integrations/class-integration-tags.php',
        'MC4WP_List_Data_Mapper' => __DIR__ . '/includes/class-list-data-mapper.php',
        'MC4WP_MailChimp' => __DIR__ . '/includes/class-mailchimp.php',
        'MC4WP_MailChimp_Subscriber' => __DIR__ . '/includes/class-mailchimp-subscriber.php',
        'MC4WP_MemberPress_Integration' => __DIR__ . '/integrations/memberpress/class-memberpress.php',
        'MC4WP_Ninja_Forms_Action' => __DIR__ . '/integrations/ninja-forms/class-action.php',
        'MC4WP_Ninja_Forms_Field' => __DIR__ . '/integrations/ninja-forms/class-field.php',
        'MC4WP_Ninja_Forms_Integration' => __DIR__ . '/integrations/ninja-forms/class-ninja-forms.php',
        'MC4WP_Ninja_Forms_V2_Integration' => __DIR__ . '/integrations/ninja-forms-2/class-ninja-forms.php',
        'MC4WP_Plugin' => __DIR__ . '/includes/class-plugin.php',
        'MC4WP_Procaptcha_Integration' => __DIR__ . '/integrations/prosopo-procaptcha/class-procaptcha-integration.php',
        'MC4WP_Procaptcha' => __DIR__ . '/integrations/prosopo-procaptcha/class-procaptcha.php',
        'MC4WP_Queue' => __DIR__ . '/includes/class-queue.php',
        'MC4WP_Queue_Job' => __DIR__ . '/includes/class-queue-job.php',
        'MC4WP_Registration_Form_Integration' => __DIR__ . '/integrations/wp-registration-form/class-registration-form.php',
        'MC4WP_Tools' => __DIR__ . '/includes/class-tools.php',
        'MC4WP_Upgrade_Routines' => __DIR__ . '/includes/admin/class-upgrade-routines.php',
        'MC4WP_User_Integration' => __DIR__ . '/includes/integrations/class-user-integration.php',
        'MC4WP_WPForms_Field' => __DIR__ . '/integrations/wpforms/class-field.php',
        'MC4WP_WPForms_Integration' => __DIR__ . '/integrations/wpforms/class-wpforms.php',
        'MC4WP_WooCommerce_Integration' => __DIR__ . '/integrations/woocommerce/class-woocommerce.php',
    ];

    if (isset($classmap[$class])) {
        require $classmap[$class];
    }
});
