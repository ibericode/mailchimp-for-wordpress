<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_User_Integration
 *
 * @access public
 * @since 2.0
 */
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
		 * Filters the merge vars which are sent to MailChimp, extracted from the user object.
		 *
		 * @since 3.0
		 *
		 * @param array $merge_vars
		 * @param WP_User $user
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_user_merge_vars', $merge_vars, $user );

		return $merge_vars;
	}

}