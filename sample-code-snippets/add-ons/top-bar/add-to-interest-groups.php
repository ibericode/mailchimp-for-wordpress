<?php

add_filter('mctb_data', function ($data) {

    // make sure we have an array to work with
    if (! isset($data['INTERESTS'])) {
        $data['INTERESTS'] = [];
    }

    // replace "interest-id" with the actual ID of your interest.
    $data['INTERESTS'][] = "interest-id";

    return $data;
});
