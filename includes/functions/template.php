<?php

if( ! defined( "MC4WP_LITE_VERSION" ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


/**
* Echoes a sign-up checkbox.
*/
function mc4wp_checkbox() {
	global $mc4wp;

	if( ! isset( $mc4wp->get_checkbox_manager()->integrations['comment_form'] ) ) {
		$mc4wp->get_checkbox_manager()->integrations['comment_form'] = new MC4WP_Comment_Form_Integration();
	}

	$mc4wp->get_checkbox_manager()->integrations['comment_form']->output_checkbox();
}

/**
* Echoes sign-up form with given $form_id.
* @param int $form_id.
*/
function mc4wp_form( $id = 0 ) {
	echo mc4wp_get_form( $id );
}

/**
* Returns HTML for sign-up form with the given $form_id.
*
* @param int $form_id.
* @return string HTML of given form_id.
*/
function mc4wp_get_form( $id = 0 ) {
	global $mc4wp;
	return $mc4wp->get_form_manager()->form( array( 'id' => $id ) );
}


/**
* Returns text with {variables} replaced.
*
* @param string $text
* @param array $list_ids Array of list id's
* @return string $text with {variables} replaced.
*/
function mc4wp_replace_variables( $text, $list_ids = array() ) {
	$needles = array( '{ip}', '{current_url}', '{date}', '{time}' );
	$replacements = array( $_SERVER['REMOTE_ADDR'], mc4wp_get_current_url(), date( "m/d/Y" ), date( "H:i:s" ) );
	$text = str_ireplace( $needles, $replacements, $text );

	// subscriber count? only fetch these if the tag is actually used
	if ( stristr( $text, '{subscriber_count}' ) != false ) {
		$subscriber_count = mc4wp_get_subscriber_count( $list_ids );
		$text = str_ireplace( '{subscriber_count}', $subscriber_count, $text );
	}

	$needles = array( '{user_email}', '{user_firstname}', '{user_lastname}', '{user_name}', '{user_id}' );
	if ( is_user_logged_in() && ( $user = wp_get_current_user() ) && ( $user instanceof WP_User ) ) {
		// logged in user, replace vars by user vars
		$user = wp_get_current_user();
		$replacements = array( $user->user_email, $user->user_firstname, $user->user_lastname, $user->display_name, $user->ID );
		$text = str_replace( $needles, $replacements, $text );
	} else {
		// no logged in user, remove vars
		$text = str_replace( $needles, '', $text );
	}

	return $text;
}

/**
* Returns number of subscribers on given lists.
*
* @param array $list_ids of list id's.
* @return int Sum of subscribers for given lists.
*/
function mc4wp_get_subscriber_count( $list_ids ) {
	$list_counts = get_transient( 'mc4wp_list_counts' );

	if ( false === $list_counts ) {
		// make api call
		$api = mc4wp_get_api();
		$lists = $api->get_lists();
		$list_counts = array();

		if ( $lists ) {

			foreach ( $lists as $list ) {
				$list_counts["{$list->id}"] = $list->stats->member_count;
			}

			$transient_lifetime = apply_filters( 'mc4wp_lists_count_cache_time', 1200 ); // 20 mins by default

			set_transient( 'mc4wp_list_counts', $list_counts, $transient_lifetime );
			set_transient( 'mc4wp_list_counts_fallback', $list_counts, 3600 * 24 ); // 1 day
		} else {
			// use fallback transient
			$list_counts = get_transient( 'mc4wp_list_counts_fallback' );
			
			if ( ! $list_counts ) { 
				return 0; 
			}
		}
	}

	// start calculating subscribers count for all list combined
	$count = 0;
	foreach ( $list_ids as $id ) {
		$count += ( isset( $list_counts[$id] ) ) ? $list_counts[$id] : 0;
	}

	return apply_filters( 'mc4wp_subscriber_count', $count );
}

/**
 * Retrieves the URL of the current WordPress page
 *
 * @return string The current URL, escaped for safe usage inside attributes.
 */
function mc4wp_get_current_url() {
	$page_url = 'http';

	if( is_ssl() ) { 
		$page_url .= 's'; 
	}

	$page_url .= '://';

	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		$request_uri = substr( $_SERVER['PHP_SELF'], 1 );

		if ( isset( $_SERVER['QUERY_STRING'] ) ) { 
			$request_uri .='?'.$_SERVER['QUERY_STRING']; 
		}
	} else {
		$request_uri = $_SERVER['REQUEST_URI'];
	}

	$page_url .= $_SERVER["HTTP_HOST"] . $request_uri;

	return esc_url( $page_url );
}



/**
* Echoes a sign-up form.
*
* @deprecated 1.3.1 Use mc4wp_form() instead.
* @see mc4wp_form()
*/
function mc4wp_show_form( $id = 0 ) {
	mc4wp_form( $id );
}

/**
* Echoes a sign-up checkbox.
*
* @deprecated 1.3.1 Use mc4wp_checkbox() instead
* @see mc4wp_checkbox()
*/
function mc4wp_show_checkbox() {
	mc4wp_checkbox();
}
