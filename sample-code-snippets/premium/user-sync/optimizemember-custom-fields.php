<?php

/**
 * This code adds support for custom fields added by OptimizeMember.
 */
add_filter( 'mc4wp_user_sync_get_user_field', function( $value, $field, $user ) {

	$custom_fields = get_user_option( 'optimizemember_custom_fields', $user->ID );

	if( $custom_fields ) {
		if( isset( $custom_fields[ $field ] ) ) {
			return $custom_fields[ $field ];
		}
	}

	return $value;
}, 10, 3 );