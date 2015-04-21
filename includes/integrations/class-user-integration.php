<?php

// prevent direct file access
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

abstract class MC4WP_User_Integration extends MC4WP_Integration {

	/**
	 * @param WP_User $user
	 *
	 * @return array
	 */
	protected function user_merge_vars( WP_User $user ) {

		// start with user_login as name, since that's always known
		$merge_vars = array(
			'NAME' => $user->user_login,
		);

		if( '' !== $user->first_name ) {
			$merge_vars['NAME'] = $user->first_name;
			$merge_vars['FNAME'] = $user->first_name;
		}

		if( '' !== $user->last_name ) {
			$merge_vars['LNAME'] = $user->last_name;
		}

		if( '' !== $user->first_name && '' !== $user->last_name ) {
			$merge_vars['NAME'] = sprintf( '%s %s', $user->first_name, $user->last_name );
		}

		/**
		 * @filter `mc4wp_user_merge_vars`
		 * @expects array
		 * @param array $merge_vars
		 * @param WP_User $user
		 *
		 * Use this to filter the merge vars of a user
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_user_merge_vars', $merge_vars, $user );

		return $merge_vars;
	}

}