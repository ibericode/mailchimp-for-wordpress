<?php

// prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Subscribe_Request extends MC4WP_Request {

	/**
	 * @var MC4WP_Field_Map
	 */
	public $map;

	/**
	 * Prepare data for MailChimp API request
	 * @return bool
	 */
	public function prepare() {
		$this->user_data = $this->guess_fields( $this->user_data );
		$this->map = new MC4WP_Field_Map( $this->user_data, $this->get_lists() );

		if( ! $this->map->success ) {
			$this->message_type = $this->map->error_code;
		}

		return $this->map->success;
	}

	/**
	 * Try to guess the values of various fields, if not given.
	 */
	protected function guess_fields() {
		return MC4WP_Tools::guess_merge_vars( $this->user_data );
	}

	/**
	 * @return bool
	 */
	public function process() {
		$api = mc4wp_get_api();

		do_action( 'mc4wp_before_subscribe', $this->user_data['EMAIL'], $this->user_data, 0 );

		$result = false;
		$email_type = $this->get_email_type();

		// loop through selected lists
		foreach ( $this->map->list_fields as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = $this->get_list_merge_vars( $list_id, $list_field_data );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $this->user_data['EMAIL'], $list_merge_vars, $email_type, $this->form->settings['double_optin'], $this->form->settings['update_existing'], $this->form->settings['replace_interests'], $this->form->settings['send_welcome'] );
			do_action( 'mc4wp_subscribe', $this->user_data['EMAIL'], $list_id, $list_merge_vars, $result, 'form', 'form', 0 );
		}

		do_action( 'mc4wp_after_subscribe', $this->user_data['EMAIL'], $this->user_data, 0, $result );

		// did we succeed in subscribing with the parsed data?
		if( ! $result ) {
			$this->message_type = ( $api->get_error_code() === 214 ) ? 'already_subscribed' : 'error';
			$this->mailchimp_error = $api->get_error_message();
		} else {
			$this->message_type = 'subscribed';

			// store user email in a cookie
			MC4WP_Tools::remember_email( $this->user_data['EMAIL'] );
		}

		$this->success = $result;

		return $result;
	}


	/**
	 * Adds global fields like OPTIN_IP, MC_LANGUAGE, OPTIN_DATE, etc to the list of user-submitted field data.
	 *
	 * @param string $list_id
	 * @param array $list_field_data
	 * @return array
	 */
	protected function get_list_merge_vars( $list_id, $list_field_data ) {

		$merge_vars = array();

		// add OPTIN_IP, we do this here as the user shouldn't be allowed to set this
		$merge_vars['OPTIN_IP'] = MC4WP_Tools::get_client_ip();

		// make sure MC_LANGUAGE matches the requested format. Useful when getting the language from WPML etc.
		if( isset( $this->map->global_fields['MC_LANGUAGE'] ) ) {
			$merge_vars['MC_LANGUAGE'] = strtolower( substr( $this->map->global_fields['MC_LANGUAGE'], 0, 2 ) );
		}

		$merge_vars = array_merge( $merge_vars, $list_field_data );

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects array
		 * @param int $form_id
		 * @param string $list_id
		 *
		 * Can be used to filter the merge variables sent to a given list
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_merge_vars', $merge_vars, 0, $list_id );

		return $merge_vars;
	}


	/**
	 * Gets the email_type
	 *
	 * @return string The email type to use for subscription coming from this form
	 */
	protected function get_email_type( ) {

		$email_type = 'html';

		// get email type from form
		if( ! empty( $this->internal_data['email_type'] ) ) {
			$email_type = sanitize_text_field( $this->internal_data['email_type'] );
		}

		// allow plugins to override this email type
		$email_type = (string) apply_filters( 'mc4wp_email_type', $email_type );

		return $email_type;
	}

}