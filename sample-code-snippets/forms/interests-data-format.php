<?php

/**
 * There are several ways to programmatically add INTERESTS data to forms.
 * This example showcases all accepted formats, from simplest to more complex.
 *
 * You can find your interest ID's in the **MailChimp for WP > MailChimp > List Overview table**.
 *
 * If you're only subscribing to a single MailChimp list, consider filtering on `mc4wp_subscriber_data` instead.
 */
add_filter( 'mc4wp_form_data', function( $data ) {

    // make sure we have an array to work with
    if( ! isset( $data['INTERESTS'] ) ) {
        $data['INTERESTS'] = array();
    }

    // by interest ID
    $data['INTERESTS'][] = "interest-id";

    // by interest ID + value (on / off)
    $data['INTERESTS'][ "interest-id" ] = false;

    // by interest category ID + interest name
    $data['INTERESTS'][ "category-id" ] = "Group";

    // or, for multiple groups
    $data['INTERESTS'][ "category-id" ] = array( "Group 1", "Group 2" );

   return $data;
});