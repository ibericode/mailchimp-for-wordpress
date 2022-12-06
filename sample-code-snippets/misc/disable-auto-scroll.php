<?php

/**
 * This snippet will disable auto scrolling of page after form submission.
 */
add_filter( 'mc4wp_form_auto_scroll', '__return_false' );