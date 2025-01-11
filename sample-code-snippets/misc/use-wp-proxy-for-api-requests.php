<?php

define('WP_PROXY_HOST', 'http://proxy-url-here.com/');
define('WP_PROXY_PORT', '80');

add_filter('pre_http_send_through_proxy', function ($use, $url) {
    if (strpos($url, 'api.mailchimp.com/') !== false) {
        return true;
    }

    // alternatively, return false here to only use proxy for conditions above
    return $use;
}, 10, 2);
