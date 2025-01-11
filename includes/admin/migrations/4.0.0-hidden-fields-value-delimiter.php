<?php

defined('ABSPATH') or exit;

/** @ignore */
function _mc4wp_400_replace_comma_with_pipe($matches)
{
    $old = $matches[1];
    $new = str_replace(',', '|', $old);
    return str_replace($old, $new, $matches[0]);
}

// get all forms
$posts = get_posts(
    [
        'post_type'   => 'mc4wp-form',
        'numberposts' => -1,
    ]
);

foreach ($posts as $post) {
    // find hidden field values in form and pass through replace function
    $old = $post->post_content;
    $new = preg_replace_callback('/type="hidden" .* value="(.*)"/i', '_mc4wp_400_replace_comma_with_pipe', $old);

    // update post if we replaced something
    if ($new != $old) {
        $post->post_content = $new;
        wp_update_post($post);
    }
}
