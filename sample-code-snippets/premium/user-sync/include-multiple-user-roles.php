<?php

/**
 * This snippet allows you to define multiple roles which should be synchronized with Mailchimp.
 */

add_filter('mc4wp_user_sync_should_sync_user', function ($sync, WP_User $user) {
    $roles_to_sync = [
        'editor',
        'customer'
    ];

    // if user has any of the above roles, return true
    $intersect = array_intersect($user->roles, $roles_to_sync);
    if (count($intersect) > 0) {
        return true;
    }

    // otherwise, return given value from plugin's own logic (eg the settings page)
    // return false here to explicitly disallow other roles from being sent to Mailchimp
    return $sync;
}, 10, 2);
