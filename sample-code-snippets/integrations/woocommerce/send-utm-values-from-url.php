<?php

// store URL values in a cookie
add_action( 'init', function() {
    $params = array( 'adid', 'marketer', 'publisher' );
    $cookie_path = '/';
    $cookie_expires = time() + (60 * 60 * 24 );
    
    foreach( $params as $key ) {
        if( ! empty( $_GET[$key] ) ) {
            setcookie( $key, $_GET[$key], $cookie_expires, $cookie_path );
        }
    }
});

// include cookie values in MailChimp data when subscribing from woocommerce checkout
add_filter( 'mc4wp_integration_woocommerce_data', function( $data ) {
    // cookie name => mailchimp field name
    $map = array(
        'adid' => 'MMERGE9',
        'marketer' => 'MMERGE7',
        'publisher' => 'MMERGE8',
    );

    foreach( $map as $cookie_name => $mailchimp_field_name ) {
        if( isset( $_COOKIE[ $cookie_name ] ) ) {
            $data[ $mailchimp_field_name ] = $_COOKIE[ $cookie_name ];
        }
    }

    return $data;
} );