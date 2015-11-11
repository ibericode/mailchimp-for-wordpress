<?php

/**
 * Class MC4WP_Form_Listener
 *
 * @since 3.0
 * @internal
 * @ignore
 */
class MC4WP_Form_Listener {

	/**
	 * @var MC4WP_Form The submitted form instance
	 */
	public $submitted_form;

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Listen for submitted forms
	 *
	 * @param $data
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
		$this->submitted_form = $form;

		// is this form valid?
		if( $form->is_valid() ) {

			// form was valid, do something
			$method = 'process_' . $form->get_action() . '_form';
			call_user_func( array( $this, $method ), $form );
		}

		$this->respond( $form );

		return true;
	}

	/**
	 * Process a subscribe form.
	 *
	 * @param MC4WP_Form $form
	 */
	public function process_subscribe_form( MC4WP_Form $form ) {
		$api = mc4wp_get_api();
		$result = false;
		$email_type = $form->get_email_type();
		$map = new MC4WP_Field_Map( $form->data, $form->get_lists() );

		// loop through selected lists
		foreach( $map->list_fields as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$merge_vars = $list_field_data;

			/**
			 * Filters merge vars which are sent to MailChimp
			 *
			 * @param array $merge_vars
			 */
			$merge_vars = (array) apply_filters( 'mc4wp_merge_vars', $merge_vars );

			/**
			 * Filters merge vars which are sent to MailChimp, only fires for form requests.
			 *
			 * @param array $merge_vars
			 */
			$merge_vars = (array) apply_filters( 'mc4wp_form_merge_vars', $merge_vars, $form );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $form->data['EMAIL'], $merge_vars, $email_type, $form->settings['double_optin'], $form->settings['update_existing'], $form->settings['replace_interests'], $form->settings['send_welcome'] );
		}

		if( ! $result ) {
			// add error code to form object
			$form->errors[] = ( $api->get_error_code() === 214 ) ? 'already_subscribed' : 'error';
			return;
		}

		/**
		 * Fires right after a form was used to subscribe.
		 *
		 * @param MC4WP_Form $form Instance of the submitted form
		 */
		do_action( 'mc4wp_form_subscribed', $form );
	}

	/**
	 * @param MC4WP_Form $form
	 */
	public function process_unsubscribe_form( MC4WP_Form $form ) {
		$api = mc4wp_get_api();
		$result = null;

		foreach( $form->get_lists() as $list_id ) {
			$result = $api->unsubscribe( $list_id, $form->data['EMAIL'] );
		}

		if( ! $result ) {
			$form->add_error( ( in_array( $api->get_error_code(), array( 215, 232 ) ) ? 'not_subscribed' : 'error' ) );
		}

		/**
		 * Fires right after a form was used to unsubscribe.
		 *
		 * @param MC4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'mc4wp_form_unsubscribed', $form );
	}

	/**
	 * @param MC4WP_Form $form
	 */
	public function respond( MC4WP_Form $form ) {

		$success = $form->has_errors();

		if( $success ) {

			/**
			 * Fires right after a form is submitted with errors.
			 *
			 * @param MC4WP_Form $form The submitted form instance.
			 */
			do_action( 'mc4wp_form_error', $form );

			// fire a dedicated event for each error
			foreach( $form->errors as $error ) {

				/**
				 * Fires right after a form was submitted with errors.
				 *
				 * The dynamic portion of the hook, `$error`, refers to the error that occured.
				 *
				 * Default errors give us the following possible hooks:
				 *
				 * - mc4wp_form_error_error                     General errors
				 * - mc4wp_form_error_spam
				 * - mc4wp_form_error_invalid_email             Invalid email address
				 * - mc4wp_form_error_already_subscribed        Email is already on selected list(s)
				 * - mc4wp_form_error_required_field_missing    One or more required fields are missing
				 * - mc4wp_form_error_no_lists_selected         No MailChimp lists were selected
				 *
				 * @param   MC4WP_Form     $form        The form instance of the submitted form.
				 */
				do_action( 'mc4wp_form_error_' . $error, $form );
			}

		} else {
			/**
			 * Fires right after a form is submitted without any errors (success).
			 *
			 * @param MC4WP_Form $form Instance of the submitted form
			 */
			do_action( 'mc4wp_form_success', $form );
		}

		/**
		 * Fires right before responding to the form request.
		 *
		 * @param MC4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'mc4wp_form_respond', $form );

		// do stuff on success (non-AJAX)
		if( $success && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

			// check if we want to redirect the visitor
			if ( '' !== $form->get_redirect_url() ) {
				wp_redirect( $form->get_redirect_url() );
				exit;
			}
		}
	}

}