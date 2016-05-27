<?php

defined( 'ABSPATH' ) or exit;

// in case the migration is _very_ late to the party
if( ! class_exists( 'MC4WP_API_v3' ) ) {
    return;
}

$options = get_option( 'mc4wp', array() );
if( empty( $options['api_key'] ) ) {
    return;
}

// get current state from transient
$lists = get_transient( 'mc4wp_mailchimp_lists_fallback' );

// for testing, store in a transient we'll never touch
set_transient( 'mc4wp_old_mailchimp_list_data', $lists, WEEK_IN_SECONDS );

if( empty( $lists ) ) {
    return;
}

$api_v3 = new MC4WP_API_v3( $options['api_key'] );
$map = array();

foreach( $lists as $list ) {

    // no groupings? easy!
    if( empty( $list->groupings ) ) {
        continue;
    }

    // prepare groupings in id => name format
    $groupings = array();
    foreach( $list->groupings as $grouping ) {
        $grouping = (array) $grouping;
        $groupings[ "" . $grouping['id'] ] = $grouping['name'];
    }

    // fetch (new) interest categories for this list
    $interests = $api_v3->get_list_interest_categories( $list->id );

    foreach( $interests as $interest ) {
        $grouping_id = array_search( $interest->title, $groupings );
        if( $grouping_id ) {
            $map[ (string) $grouping_id ] = $interest->id;
        }
    }

}

if( ! empty( $map ) ) {
    update_option( 'mc4wp_groupings_map', $map );
}

// delete old transient
delete_transient( 'mc4wp_mailchimp_lists' );