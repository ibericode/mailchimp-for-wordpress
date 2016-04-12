<?php

defined( 'ABSPATH' ) or exit;

// this depends on previous migration
$map = get_option( 'mc4wp_groupings_map', null );
if( empty( $map ) ) {
    return;
}

// prepare replacements
$replacements = array();
foreach( $map as $old_id => $new_id ) {
    $replacements['GROUPINGS['. $old_id.']'] = 'INTERESTS['. $new_id.']';
}

// update all posts
$posts = get_posts(
    array(
        'post_type' => 'mc4wp-form',
        'post_status' => 'publish',
        'numberposts' => -1
    )
);

foreach( $posts as $post ) {
    $post->post_content = str_ireplace( array_keys( $replacements ), array_values( $replacements ), $post->post_content );
    wp_update_post( $post );
}

