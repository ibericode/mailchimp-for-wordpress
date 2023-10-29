<?php

add_filter( 'mctb_show_bar', function() {
    // don't show for posts with category "fruit"
    if (is_single() && has_category(array('fruit'))) {
        return false;
    }
});
