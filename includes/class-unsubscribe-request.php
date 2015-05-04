<?php

// prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Unsubscribe_Request extends MC4WP_Request {
	/**
	 * @return bool
	 */
	public function process() {

		$api = mc4wp_get_api();
		$result = false;

		foreach( $this->get_lists() as $list_id ) {
			$result = $api->unsubscribe( $list_id, $this->data['EMAIL'] );
		}

		if( ! $result ) {
			$this->mailchimp_error = $api->get_error_message();
			$this->message_type = ( $api->get_error_code() === 215 ) ? 'not_subscribed' : 'error';
		} else {
			$this->message_type = 'unsubscribed';
		}

		$this->success = $result;

		return $result;
	}

	/**
	 *
	 */
	public function prepare() {
		return true;
	}
}