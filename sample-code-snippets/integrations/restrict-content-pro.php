<?php
/*
Plugin Name: Restrict Content Pro - MailChimp User Sync
Description: Sync RCP members with MailChimp (opt-in)
Version: 1.0
Author: Danny van Kooten
Author URI: http://dvk.co/
*/

add_action('rcp_after_password_registration_field', 'rcp_mailchimp_add_profile_fields');
add_action('rcp_profile_editor_after', 'rcp_mailchimp_add_profile_fields');
add_action('rcp_form_processing', 'rcp_mailchimp_save_profile_fields', 10, 2);
add_action('rcp_user_profile_updated', 'rcp_mailchimp_save_profile_fields', 10);

add_filter('mailchimp_sync_should_sync_user', 'rcp_mailchimp_should_subscribe', 10, 2);
add_filter('mailchimp_sync_subscriber_data', 'rcp_mailchimp_user_data', 10, 2);

function rcp_mailchimp_add_profile_fields($user_id = 0)
{
    $selected = $user_id && get_user_meta($user_id, 'rcp_mailchimp', true);

    ?>
    <br />
    <p>
        <label style="width: 100%; float: none;"><input type="checkbox" name="rcp_mailchimp" value="1" <?php checked($selected); ?>> Subscribe to our newsletter?</label>
    </p>
    <?php
}

function rcp_mailchimp_save_profile_fields($posted, $user_id)
{

    if (is_numeric($posted)) {
        $user_id = $posted;
        $posted = $_POST;
    }

    if ($posted['rcp_mailchimp']) {
        update_user_meta($user_id, 'rcp_mailchimp', 1);
    } else {
        delete_user_meta($user_id, 'rcp_mailchimp');
    }
}

function rcp_mailchimp_should_subscribe($should, $user)
{

    if (! $should) {
        return false;
    }

    $should = (bool) get_user_meta($user->ID, 'rcp_mailchimp', true);

    return $should;
}

function rcp_mailchimp_subscriber_data(MC4WP_MailChimp_Subscriber $subscriber, WP_User $user)
{

    // map of RCP subscription ID => MailChimp interest ID
    $map = [
        '1' => "interest-id-1",     // Trade Membership
        '2' => "interest-id-2",     // Artist Membership
        '3' => "interest-id-3",     // Associate Membership
        '4' => "d8v1k2ilx",         // Corporate Membership
        '5' => "a0192m3az",         // Supporter Membership
    ];

    // get RCP subscription ID
    $subscription_id = rcp_get_subscription_id($user->ID);

    // see if this maps to a known interest
    if (isset($map[ $subscription_id ])) {
        $interest_id = $map[ $subscription_id ];
        $subscriber->interests[ $interest_id ] = true;
    }

    // return modified subscriber object
    return $subscriber;
}


