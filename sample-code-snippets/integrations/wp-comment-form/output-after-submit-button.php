<?php

/**
 * This snippet will remove the hook that outputs the mc4wp checkbox BEFORE the submit button.
 *
 * This way, it falls back to being outputted after the submit button.
 */

// only run once templates are loaded
add_action( 'template_redirect', function() {

	// make sure mc4wp is activated
	if( ! function_exists( 'mc4wp' ) ) { return; }

	// get integration manager
	$integrations = mc4wp('integrations');
	if( ! $integrations instanceof MC4WP_Integration_Manager ) {
		return;
	}

	// get comment form integration
	$comment_form_integration = $integrations->get('wp-comment-form');
	remove_filter( 'comment_form_submit_field', array( $comment_form_integration, 'add_checkbox_before_submit_button' ), 90 );
} );