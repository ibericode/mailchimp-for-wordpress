<?php

/**
* Replaces {MY_POST_OPTIONS} in the form content with a list of <option> elements
*/

add_filter('mc4wp_form_content', function ($content) {
    $posts = get_posts([ 'post_status' => 'publish' ]);
    $options = [];
    foreach ($posts as $post) {
        $options[] = sprintf('<option value="%d">%s</option>', $post->ID, $post->post_title);
    }
    $options = join('', $options);
    $content = str_replace('{MY_POST_OPTIONS}', $options, $content);
    return $content;
});
