<?php

// prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Subscribe_Request extends MC4WP_Request {

	/**
	 * @var array
	 */
	private $list_fields_map = array();

	/**
	 * @var array
	 */
	private $unmapped_fields = array();

	/**
	 * @var array
	 */
	private $global_fields = array();

	/**
	 * Prepare data for MailChimp API request
	 * @return bool
	 */
	public function prepare() {
		$this->guess_fields();
		$mapped = $this->map_data();
		return $mapped;
	}

	/**
	 * Try to guess the values of various fields, if not given.
	 */
	protected function guess_fields() {
		// add some data to the posted data, like FNAME and LNAME
		$this->user_data = MC4WP_Tools::guess_merge_vars( $this->user_data );
	}

	/**
	 * Maps the received data to MailChimp lists
	 *
	 * @return array
	 */
	protected function map_data() {

		$mapper = new MC4WP_Field_Mapper( $this->user_data, $this->get_lists() );

		if( $mapper->success ) {
			$this->list_fields_map = $mapper->get_list_fields_map();
			$this->global_fields = $mapper->get_global_fields();
			$this->unmapped_fields = $mapper->get_unmapped_fields();
		} else {
			$this->message_type = $mapper->get_error_code();
		}

		return $mapper->success;
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
		foreach ( $this->list_fields_map as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = $this->get_list_merge_vars( $list_id, $list_field_data );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $this->user_data['EMAIL'], $list_merge_vars, $email_type, $this->form->settings['double_optin'], $this->form->settings['update_existing'], $this->form->settings['replace_interests'], $this->form->settings['send_welcome'] );
			do_action( 'mc4wp_subscribe', $this->user_data['EMAIL'], $list_id, $list_merge_vars, $result, 'form', 'form', 0 );
		}

		do_action( 'mc4wp_after_subscribe', $this->user_data['EMAIL'], $this->user_data, 0, $result );

		// did we succeed in subscribing with the parsed data?
		if( ! $result ) {
			// don't grab mailchimp error if status code is "already_subscribed"
			if( $api->get_error_code() === 214 ) {
				$this->message_type = 'already_subscribed';
			} else {
				$this->message_type = 'error';
				$this->mailchimp_error = $api->get_error_message();
			}
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
		$merge_vars['OPTIN_IP'] = MC4WP_tools::get_client_ip();

		// make sure MC_LANGUAGE matches the requested format. Useful when getting the language from WPML etc.
		if( isset( $this->global_fields['MC_LANGUAGE'] ) ) {
			$merge_vars['MC_LANGUAGE'] = strtolower( substr( $this->global_fields['MC_LANGUAGE'], 0, 2 ) );
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
		$merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, 0, $list_id );

		return (array) $merge_vars;
	}


	/**
	 * Gets the email_type
	 *
	 * @return string The email type to use for subscription coming from this form
	 */
	protected function get_email_type( ) {

		$email_type = 'html';

		// get email type from form
		if( isset( $this->user_data['_MC4WP_EMAIL_TYPE'] ) ) {
			$email_type = sanitize_text_field( $this->user_data['_MC4WP_EMAIL_TYPE'] );
		}

		// allow plugins to override this email type
		$email_type = apply_filters( 'mc4wp_email_type', $email_type );

		return (string) $email_type;
	}

}