<?php

/**
 * Class MC4WP_Form_Listener
 *
 * @since 3.0
 * @internal
 */
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

		$request = MC4WP_Request::create_from_globals();

		try {
			$form = mc4wp_get_form( $request->params->get( '_mc4wp_form_id' ) );
		} catch( Exception $e ) {
			return false;
		}

		// where the magic happens
		$form->handle_request( $request );

		// is this form valid?
		if( $form->is_valid() ) {

			// form was valid, do something
			$this->process( $form );
		}

		$this->respond( $form );

		return true;
	}

	/**
	 * @param MC4WP_Form $form
	 * @todo remember email
	 * @return bool
	 */
	public function process( MC4WP_Form $form ) {

		$api = mc4wp_get_api();
		$result = false;

		// @todo determine type of request (subscribe / unsubscribe )
		$email_type = $form->get_email_type();
		$map = new MC4WP_Field_Map( $form->data, $form->get_lists() );

		// loop through selected lists
		foreach( $map->list_fields as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$merge_vars = $list_field_data;

			/**
			 * @filter `mc4wp_merge_vars`
			 * @expects array
			 *
			 * Can be used to filter the merge variables sent to a given list
			 */
			$merge_vars = (array) apply_filters( 'mc4wp_merge_vars', $merge_vars );
			$merge_vars = (array) apply_filters( 'mc4wp_form_merge_vars', $merge_vars, $form );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $form->data['EMAIL'], $merge_vars, $email_type, $form->settings['double_optin'], $form->settings['update_existing'], $form->settings['replace_interests'], $form->settings['send_welcome'] );
			do_action( 'mc4wp_after_subscribe', $form->data['EMAIL'], $merge_vars, $result );
		}

		if( ! $result ) {
			// add error code to form object
			$form->errors[] = ( $api->get_error_code() === 214 ) ? 'already_subscribed' : 'error';
			return;
		}

		// yay! success!
		do_action( 'mc4wp_form_subscribed', $form );

		// @todo move to filter
		MC4WP_Tools::remember_email( $form->data['EMAIL'] );
	}

	/**
	 * @param MC4WP_Form $form
	 */
	public function respond( MC4WP_Form $form ) {

		do_action( 'mc4wp_form_respond_to_request', $form );

		// do stuff on success, non-AJAX only
		if( empty( $form->errors ) ) {

			/**
			 * @action mc4wp_form_success
			 *
			 * Use to hook into successful form sign-ups
			 *
			 * @param   int     $form        The form object
			 */
			do_action( 'mc4wp_form_success', $form );

			// check if we want to redirect the visitor
			if ( '' !== $form->get_redirect_url() ) {
				wp_redirect( $form->get_redirect_url() );
				exit;
			}

			return;
		}

		/**
		 * @action mc4wp_form_error
		 * @param MC4WP_Form $form
		 */
		do_action( 'mc4wp_form_error', $form );

		// fire a dedicated event for each error
		foreach( $form->errors as $error ) {

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
			 * @param   MC4WP_Form     $form        The form object
			 */
			do_action( 'mc4wp_form_error_' . $error, $form );
		}

	}

}