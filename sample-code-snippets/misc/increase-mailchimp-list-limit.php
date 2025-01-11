<?php

/**
 * Tell the plugin to increase the MailChimp list limit to 500 (default is 200).
 */

add_filter('mc4wp_mailchimp_list_limit', function ($limit) {
    return 500;
});
