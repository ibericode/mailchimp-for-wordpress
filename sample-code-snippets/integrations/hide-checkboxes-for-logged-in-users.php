<?php

/**
 * This snippet will print some CSS if a user is logged-in.
 *
 * The CSS will hide the sign-up checkboxes.
 */
function myprefix_hide_checkbox_for_logged_in_users()
{
    // get current user
    if (is_user_logged_in()) {
        ?>
        <style type="text/css">
            #mc4wp-checkbox,
            .mc4wp-checkbox {
                display: none !important;
            }
        </style>
        <?php
    }
}

add_action('wp_head', 'myprefix_hide_checkbox_for_logged_in_users', 70);