<?php
return array(
	'subscribed'               => array(
		'type' => 'success',
		'text' => __( 'Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'phplist-for-wp' )
	),
	'updated' 				   => array(
		'type' => 'success',
		'text' => __( 'Thank you, your records have been updated!', 'phplist-for-wp' ),
	),
	'unsubscribed'             => array(
		'type' => 'success',
		'text' => __( 'You were successfully unsubscribed.', 'phplist-for-wp' ),
	),
	'not_subscribed'           => array(
		'type' => 'notice',
		'text' => __( 'Given email address is not subscribed.', 'phplist-for-wp' ),
	),
	'error'                    => array(
		'type' => 'error',
		'text' => __( 'Oops. Something went wrong. Please try again later.', 'phplist-for-wp' ),
	),
	'invalid_email'            => array(
		'type' => 'error',
		'text' => __( 'Please provide a valid email address.', 'phplist-for-wp' ),
	),
	'already_subscribed'       => array(
		'type' => 'notice',
		'text' => __( 'Given email address is already subscribed, thank you!', 'phplist-for-wp' ),
	),
	'required_field_missing'   => array(
		'type' => 'error',
		'text' => __( 'Please fill in the required fields.', 'phplist-for-wp' ),
	),
	'no_lists_selected'        => array(
		'type' => 'error',
		'text' => __( 'Please select at least one list.', 'phplist-for-wp' )
	),
);
