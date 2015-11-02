<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Subscribe_Request
 *
 * @todo Normalize public properties
 */
class MC4WP_Subscribe_Request extends MC4WP_Form_Request {

	/**
	 * @var MC4WP_Field_Map
	 */
	public $map;


	/**
	 * Prepare data for MailChimp API request
	 * @return bool
	 */
	public function prepare() {
		try{
			$this->map = new MC4WP_Field_Map( $this->user_data, $this->get_lists() );
		} catch( Exception $e ) {
			if( $e->getCode() === 400 ) {
				$this->result_code = 'required_field_missing';
				return false;
			};
		}

		return true;
	}

	/**
	 * @return bool
	 *
	 * @todo Normalize actions & parameters.
	 */
	public function process() {
		$api = mc4wp_get_api();

		// @todo fix this parameter
		do_action( 'mc4wp_before_subscribe', $this->user_data['EMAIL'], $this->user_data, 0 );

		$result = false;
		$email_type = $this->get_email_type();

		// loop through selected lists
		foreach( $this->map->list_fields as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = $this->get_list_merge_vars( $list_id, $list_field_data );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $this->user_data['EMAIL'], $list_merge_vars, $email_type, $this->form->settings['double_optin'], $this->form->settings['update_existing'], $this->form->settings['replace_interests'], $this->form->settings['send_welcome'] );
			do_action( 'mc4wp_subscribe', $this->user_data['EMAIL'], $list_id, $list_merge_vars, $result, 'form', 'form', 0 );
		}

		do_action( 'mc4wp_after_subscribe', $this->user_data['EMAIL'], $this->user_data, 0, $result );

		// did we succeed in subscribing with the parsed data?
		if( ! $result ) {
			$this->result_code = ( $api->get_error_code() === 214 ) ? 'already_subscribed' : 'error';
			$this->mailchimp_error = $api->get_error_message();
		} else {
			$this->result_code = 'subscribed';

			do_action( 'mc4wp_form_subscribed', $this );

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

		// make sure MC_LANGUAGE matches the requested format. Useful when getting the language from WPML etc.
		if( isset( $this->map->global_fields['MC_LANGUAGE'] ) ) {
			$merge_vars['MC_LANGUAGE'] = strtolower( substr( $this->map->global_fields['MC_LANGUAGE'], 0, 2 ) );
		}

		$merge_vars = array_merge( $merge_vars, $list_field_data );

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects array
		 *
		 * Can be used to filter the merge variables sent to a given list
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_merge_vars', $merge_vars );
		$merge_vars = (array) apply_filters( 'mc4wp_form_merge_vars', $merge_vars, $this->form );

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