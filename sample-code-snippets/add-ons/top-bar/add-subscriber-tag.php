<?php

/**
 * Echo a susbcriber tag
 */
 
 add_filter( 'mctb_subscriber_data', function( $subscriber ) {
    $subscriber->tags[] = 'My tag';
    return $subscriber;
});
