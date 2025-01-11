<?php

add_filter('mc4wp_subscriber_count', function ($count, $list_ids) {
    $count = get_transient('guillaume_subscriber_count');

    if ($count === false) {
        $api = mc4wp_get_api_v3();
        $count = 0;
        foreach ($list_ids as $list_id) {
            $data = $api->get_list($list_id, ['fields' => 'stats']);
            $count += $data->stats->member_count + $data->stats->unsubscribe_count;
        }
        set_transient('guillaume_subscriber_count', $count, 60);
    }
    return (int) $count;
}, 10, 2);
