<?php

add_filter('mc4wp_integration_wpforms_data', function($data) {
    $data['FNAME'] = $_POST['wpforms']['fields'][4];
    return $data;
});
