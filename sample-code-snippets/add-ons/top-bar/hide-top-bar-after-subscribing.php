<?php

/**
* Set cookie when Top Bar is used to subscribe
*/
add_action( 'mctb_subscribed', function() {
   $expires = time() + ( 60 * 60 * 24 * 30 ); // 30 days
   setcookie( 'mctb_hide_bar', '1', $expires, '/' );
});

/**
* Do not load Top Bar when cookie exists
*/
add_filter( 'mctb_show_bar', function( $show ) {
   return $show && empty( $_COOKIE['mctb_hide_bar'] );
});
