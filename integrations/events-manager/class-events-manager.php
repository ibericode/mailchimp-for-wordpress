<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Events_Manager_Integration
 *
 * @ignore
 */
class MC4WP_Events_Manager_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	public $name = "Events Manager";

	/**
	 * @var string
	 */
	public $description = "Subscribes people from Events Manager booking forms.";


	/**
	 * Add hooks
	 */
	public function add_hooks() {

		if( ! $this->options['implicit'] ) {
			add_action( 'em_booking_form_footer', array( $this, 'output_checkbox' ) );
		}

		add_action( 'em_bookings_added', array( $this, 'subscribe_from_events_manager' ), 5 );
	}



	/**
	 * Subscribe from Events Manager booking forms.
	 *
	 * @param EM_Booking $args
	 * @return bool
	 */
	public function subscribe_from_events_manager( $args ) {

		// Is this integration triggered? (checkbox checked or implicit)
		if( ! $this->triggered() ) {
			return false;
		}

		$em_data = $this->get_data();

		// logged-in users do not have these form fields, so grab from user object instead
		if( empty( $em_data['user_email'] ) && is_user_logged_in() ) {
			$user = wp_get_current_user();
			$em_data['user_email'] = $user->user_email;
			$em_data['user_name'] = sprintf("%s %s", $user->first_name, $user->last_name );
		}

		if( empty( $em_data['user_email'] ) ) {
			return false;
		}

		$data = array(
			'EMAIL' => $em_data['user_email'],
			'NAME' => $em_data['user_name']
		);

		// subscribe using email and name
		return $this->subscribe( $data, $args->booking_id );

	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return defined( 'EM_VERSION' );
	}


}
