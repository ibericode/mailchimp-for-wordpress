<?php

add_filter('mc4wp_post_campaign_post_types', function ($types) {
    // also show the "Create Mailchimp campaign" section for post types of type page
    $types[] = 'page';
    return $types;
});
