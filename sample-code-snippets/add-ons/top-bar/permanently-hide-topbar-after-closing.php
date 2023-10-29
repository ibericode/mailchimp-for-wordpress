<?php

/**
* Set cookie when Top Bar is used to subscribe
*/

add_filter( 'mctb_show_bar', function() {
    return !isset($_COOKIE['mctb_bar_hidden']);
});
