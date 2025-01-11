<?php

add_filter('mc4wp_integration_woocommerce_lists', function ($lists) {
    $user = wp_get_current_user();

    if (! $user) {
        return $lists;
    }

    // map of user roles => mailchimp list ID's
    $map = [
        'subscriber' => 'list-id-1',
        'customer' => 'list-id-2',
        'editor' => 'list-id-3',
    ];

    // get user role
    $user_role = array_shift($user->roles);

    // use custom list if set for this role
    if (isset($map[ $user_role ])) {
        return $map[ $user_role ];
    }

    return $lists;
});
