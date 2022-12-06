<?php

/**
 * Insert a sign-up form after the 3rd paragraph.
 */
add_filter( 'the_content', function( $content ) {

	if( is_single() ) {
		$content = mc4wp_insert_after_paragraph( $content, mc4wp_get_form(), 2 );
	}

	return $content;
});



/**
 * Helper function to insert a string after the n-th paragraph
 *
 * @param string $content The original content
 * @param string $insertion
 * @param int $paragraph_number The paragraph to insert the string after. Default is 2.
 *
 * @return string
 */

function mc4wp_insert_after_paragraph( $content, $insertion, $paragraph_number = 2 ) {
	static $closing_p = '</p>';

	$paragraphs = explode( $closing_p, $content );
	$new_content = '';
	$target_index = $paragraph_number - 1;

	foreach( $paragraphs as $index => $paragraph ) {
		$new_content .= $paragraph;

		if( $index == $target_index ) {
			$new_content .= $insertion;
		}
	}

	return $new_content;
}
