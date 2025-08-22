<?php

add_filter('mc4wp_form_response_html', function ($html) {
    $coupon_code = '....'; // logic for retrieving or generating a coupon code goes here
    $html = str_replace('%%COUPON_CODE%%', $coupon_code, $html);
    return $html;
});
