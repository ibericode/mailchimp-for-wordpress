<?php

class MC4WP_Form_Listener {

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function listen( array $data ) {

		if( ! isset( $data['_mc4wp_form_id'] ) ) {
			return false;
		}

		try {
			$request = MC4WP_Request::create_from_globals();
			$form = mc4wp_get_form( $request->get( '_mc4wp_form_id' ) );
		} catch( Exception $e ) {
			return false;
		}

		$form->handle_request( $request );


		if( $form->is_valid() ) {
			// form was valid, do something
			$this->process( $request, $form );
		}

		if( $request->is_ajax() ) {
			$this->respond( $request, $form );
		}

		return true;
	}

	/**
	 * @param MC4WP_Request $request
	 * @param MC4WP_Form $form
	 * @todo add filter & action hooks back in
	 * @todo remember email
	 * @return bool
	 */
	public function process( MC4WP_Request $request, MC4WP_Form $form ) {

		$api = mc4wp_get_api();
		$result = false;

		// @todo determine type of request (subscribe / unsubscribe )
		$email_type = $form->get_email_type();
		$map = new MC4WP_Field_Map( $request->all(), $form->get_lists() );

		// loop through selected lists
		foreach( $map->list_fields as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$merge_vars = $list_field_data;

			// @todo fix filter
			//$list_merge_vars = $this->get_list_merge_vars( $list_id, $list_field_data );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $request->get('EMAIL'), $merge_vars, $email_type, $form->settings['double_optin'], $form->settings['update_existing'], $form->settings['replace_interests'], $form->settings['send_welcome'] );
		}

		if( ! $result ) {
			// add error code to form object
			$form->errors[] = ( $api->get_error_code() === 214 ) ? 'already_subscribed' : 'error';
		}

		// yay! success!
		// @todo fire events for email notification etc.
	}

	/**
	 * @param $request
	 * @param $form
	 */
	public function respond( MC4WP_Request $request, MC4WP_Form $form ) {

		do_action( 'mc4wp_form_respond_to_request', $this );

		// do stuff on success, non-AJAX only
		if( empty( $form->errors ) ) {

			/**
			 * @action mc4wp_form_success
			 *
			 * Use to hook into successful form sign-ups
			 *
			 * @param   int     $form_id        The ID of the submitted form (PRO ONLY)
			 * @param   string  $email          The email of the subscriber
			 * @param   array   $data           Additional list fields, like FNAME etc (if any)
			 */
			do_action( 'mc4wp_form_success', $form, $request->all() );

			// check if we want to redirect the visitor
			if ( '' !== $form->get_redirect_url() ) {
				wp_redirect( $form->get_redirect_url() );
				exit;
			}
			return;

		}

		/**
		 * @action mc4wp_form_error_{ERROR_CODE}
		 *
		 * Use to hook into various sign-up errors. Hook names are:
		 *
		 * - mc4wp_form_error_error                     General errors
		 * - mc4wp_form_error_invalid_email             Invalid email address
		 * - mc4wp_form_error_already_subscribed        Email is already on selected list(s)
		 * - mc4wp_form_error_required_field_missing    One or more required fields are missing
		 * - mc4wp_form_error_no_lists_selected         No MailChimp lists were selected
		 *
		 * @param   int     $form_id        The ID of the submitted form (PRO ONLY)
		 * @param   string  $email          The email of the subscriber
		 * @param   array   $data           Additional list fields, like FNAME etc (if any)
		 */
		//do_action( 'mc4wp_form_error_' . $this->result_code, 0, $this->user_data['EMAIL'], $this->user_data );

	}

	/**
	 * Adds global fields like OPTIN_IP, MC_LANGUAGE, OPTIN_DATE, etc to the list of user-submitted field data.
	 *
	 * @todo fix this method
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

}