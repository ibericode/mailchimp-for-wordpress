<?php

/**
* Increase the HTTP request timeout to 30 seconds
*/
add_filter( 'mc4wp_http_request_args', function( $args ) {
   $args['timeout'] = 30;
   return $args;
});
