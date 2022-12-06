<?php

/**
 * Insert a sign-up form after the post content.
 */
add_filter( 'the_content', function( $content ) {

	if( is_single() ) {
		$content .= mc4wp_get_form();
	}

	return $content;
});


