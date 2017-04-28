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
        $data = array(
			'EMAIL' => $user->user_email,
			'NAME' => $user->user_login,
		);

		if( '' !== $user->first_name ) {
            $data['NAME'] = $user->first_name;
            $data['FNAME'] = $user->first_name;
		}

		if( '' !== $user->last_name ) {
            $data['LNAME'] = $user->last_name;
		}

		if( '' !== $user->first_name && '' !== $user->last_name ) {
            $data['NAME'] = sprintf( '%s %s', $user->first_name, $user->last_name );
		}

		/**
		 * @since 3.0
		 * @deprecated 4.0
         * @ignore
		 */
		$data = (array) apply_filters( 'mc4wp_user_merge_vars', $data, $user );

		return $data;
	}

}