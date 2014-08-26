<?php

// prevent direct file access
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Events_Manager_Integration extends MC4WP_General_Integration {

	protected $type = 'events_manager';

	public function __construct() {
		add_action( 'em_bookings_added', array( $this, 'subscribe_from_events_manager' ) );
	}

	/**
	 * Subscribe from Events Manager booking forms.
	 *
	 * @param array $args
	 */
	public function subscribe_from_events_manager( $em_booking ) {

		// was sign-up checkbox checked?
		if( ! isset( $em_booking->booking_meta['booking']['mc4wp-subscribe'] ) || $em_booking->booking_meta['booking']['mc4wp-subscribe'] != 1 ) {
			return false;
		}

		// find email field
		if( isset( $em_booking->booking_meta['registration']['user_email'] ) ) {

			$meta = $em_booking->booking_meta;

			$email = $meta['registration']['user_email'];
			$merge_vars = array();

			// find name fields
			if( isset( $meta['registration']['user_name'] ) ) {
				$merge_vars['NAME'] = $meta['registration']['user_name'];
			}

			if( isset( $meta['registration']['first_name'] ) ) {
				$merge_vars['FNAME'] = $meta['registration']['first_name'];
			}

			if( isset( $meta['registration']['last_name'] ) ) {
				$merge_vars['LNAME'] = $meta['registration']['last_name'];
			}

			if( is_array( $meta['booking'] ) ) {
				foreach( $meta['booking'] as $field_name => $field_value ) {

					// only add fields starting with mc4wp-
					if( strtolower( substr( $field_name, 0, 6 ) ) !== 'mc4wp-' || $field_name === 'mc4wp-subscribe' ) {
						continue;
					}

					$field_name = strtoupper( substr( $field_name, 6 ) );

					// add to merge vars
					$merge_vars[ $field_name ] = $field_value;
				}
			}

			// subscribe using email and name
			return $this->subscribe( $email, $merge_vars );
		}

		// try general fallback
		return $this->try_subscribe();
	}

}