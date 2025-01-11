<?php

/**
 * Tell MailChimp for WordPress to subscribe to a certain list based on the WPML language that is being viewed.
 *
 * Make sure to change the list ID's to the actual ID's of your MailChimp lists
 *
 * @param array $lists
 * @return array $lists
 */
function myprefix_filter_mc4wp_lists($lists)
{
    $list_id_spanish_list = '123abcdef456';
    $list_id_english_list = '456defabc123';

    if (defined('ICL_LANGUAGE_CODE')) {
        switch (ICL_LANGUAGE_CODE) {
            // spanish
            case 'es':
                $lists = [ $list_id_spanish_list ];
                break;
            // english
            case 'en':
                $lists = [ $list_id_english_list ];
                break;
        }
    }
    return $lists;
}

add_filter('mc4wp_lists', 'myprefix_filter_mc4wp_lists');
