<?php

/**
 * Add custom BuddyPress registration fields to MailChimp.
 *
 * - Replace "CUST1" with your MailChimp field name.
 * - Replace "13" with your BuddyPress field ID.
 */

add_filter('mc4wp_integration_buddypress_data', function ($data, $user_id) {
    $data['CUST1'] = xprofile_get_field_data(13, $user_id);
    return $data;
}, 10, 2);
