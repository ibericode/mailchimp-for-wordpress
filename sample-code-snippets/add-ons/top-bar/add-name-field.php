<?php

/**
 * Echo a NAME field just before the submit button.
 */
add_action( 'mctb_before_submit_button', function() {
	echo '<input type="text" name="NAME" placeholder="Your name" />';
});


/**
 * Make sure the content of the NAME field is sent to MailChimp
 *
 * @param array $vars
 */
add_filter( 'mctb_data', function( $vars ) {
	$vars['NAME'] = ( isset( $_POST['NAME'] ) ) ? sanitize_text_field( $_POST['NAME'] ) : '';
	return $vars;
});
