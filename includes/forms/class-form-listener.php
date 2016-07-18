<?php

/**
 * Class MC4WP_Form_Listener
 *
 * @since 3.0
 * @access private
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
	 * @param MC4WP_Request $request
	 * @return bool
	 */
	public function listen( MC4WP_Request $request ) {

		if( ! $request->post->get( '_mc4wp_form_id' ) ) {
			return false;
		}

		try {
			$form = mc4wp_get_form( $request->post->get( '_mc4wp_form_id' ) );
		} catch( Exception $e ) {
			return false;
		}

		// where the magic happens
		$form->handle_request( $request );
		$form->validate();

		// store submitted form
		$this->submitted_form = $form;

		// did form have errors?
		if( ! $form->has_errors() ) {

			// form was valid, do something
			$method = 'process_' . $form->get_action() . '_form';
			call_user_func( array( $this, $method ), $form );
		} else {
			$this->get_log()->info( sprintf( "Form %d > Submitted with errors: %s", $form->ID, join( ', ', $form->errors ) ) );
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
		$api = $this->get_api();
		$result = false;
		$email = $form->data['EMAIL'];
		$email_type = $form->get_email_type();
		$merge_vars = $form->data;

		/**
		 * Filters merge vars which are sent to MailChimp, only fires for form requests.
		 *
		 * @param array $merge_vars
		 * @param MC4WP_Form $form
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_form_merge_vars', $merge_vars, $form );

		// create a map of all lists with list-specific merge vars
		$map = new MC4WP_Field_Map( $merge_vars, $form->get_lists() );

		// loop through lists
		foreach( $map->list_fields as $list_id => $merge_vars ) {
			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $email, $merge_vars, $email_type, $form->settings['double_optin'], $form->settings['update_existing'], $form->settings['replace_interests'], $form->settings['send_welcome'] );
		}

		// do stuff on failure
		if( ! $result ) {

			$error_code_unsubscribed = 212;
			$error_code_bounced = 213;
			if( $api->get_error_code() == $error_code_unsubscribed || $api->get_error_code() == $error_code_bounced ) {
				$form->errors[] = 'previously_unsubscribed';
				$this->get_log()->warning( sprintf( 'Form %d > %s has unsubscribed before and cannot be resubscribed by the plugin.', $form->ID, $form->data['EMAIL'] ) );
			} elseif( $api->get_error_code() == 214 ) {
				// handle "already_subscribed" as a soft-error
				$form->errors[] = 'already_subscribed';
				$this->get_log()->warning( sprintf( "Form %d > %s is already subscribed to the selected list(s)", $form->ID, mc4wp_obfuscate_string( $form->data['EMAIL'] ) ) );
			} else {
				// log error
				$this->get_log()->error( sprintf( 'Form %d > MailChimp API error: %s %s', $form->ID, $api->get_error_code(), $api->get_error_message() ) );

				// add error code to form object
				$form->errors[] = 'error';
			}

			// bail
			return;
		}

		$this->get_log()->info( sprintf( "Form %d > Successfully subscribed %s", $form->ID, $form->data['EMAIL'] ) );

		/**
		 * Fires right after a form was used to subscribe.
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Form $form Instance of the submitted form
		 * @param string $email
		 * @param array $merge_vars
		 * @param array $pretty_data
		 */
		do_action( 'mc4wp_form_subscribed', $form, $email, $merge_vars, $map->pretty_data );
	}

	/**
	 * @param MC4WP_Form $form
	 */
	public function process_unsubscribe_form( MC4WP_Form $form ) {
		$api = $this->get_api();
		$result = null;

		foreach( $form->get_lists() as $list_id ) {
			$result = $api->unsubscribe( $list_id, $form->data['EMAIL'] );
		}

		if( ! $result ) {
			// not subscribed is a soft-error
			if( in_array( $api->get_error_code(), array( 215, 232 ) ) ) {
				$form->add_error( 'not_subscribed' );
				$this->get_log()->info( sprintf( 'Form %d > %s is not subscribed to the selected list(s)', $form->ID, $form->data['EMAIL'] ) );
			} else {
				$form->add_error( 'error' );
				$this->get_log()->error( sprintf( 'Form %d > MailChimp API error: %s', $form->ID, $api->get_error_message() ) );
			}
		}

		/**
		 * Fires right after a form was used to unsubscribe.
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'mc4wp_form_unsubscribed', $form );
	}

	/**
	 * @param MC4WP_Form $form
	 */
	public function respond( MC4WP_Form $form ) {

		$success = ! $form->has_errors();

		if( $success ) {

			/**
			 * Fires right after a form is submitted without any errors (success).
			 *
			 * @since 3.0
			 *
			 * @param MC4WP_Form $form Instance of the submitted form
			 */
			do_action( 'mc4wp_form_success', $form );

		} else {

			/**
			 * Fires right after a form is submitted with errors.
			 *
			 * @since 3.0
			 *
			 * @param MC4WP_Form $form The submitted form instance.
			 */
			do_action( 'mc4wp_form_error', $form );

			// fire a dedicated event for each error
			foreach( $form->errors as $error ) {

				/**
				 * Fires right after a form was submitted with errors.
				 *
				 * The dynamic portion of the hook, `$error`, refers to the error that occurred.
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
				 * @since 3.0
				 *
				 * @param   MC4WP_Form     $form        The form instance of the submitted form.
				 */
				do_action( 'mc4wp_form_error_' . $error, $form );
			}

		}

		/**
		 * Fires right before responding to the form request.
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'mc4wp_form_respond', $form );

		// do stuff on success (non-AJAX only)
		if( $success && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

			// do we want to redirect?
			$redirect_url = $form->get_redirect_url();
			if ( ! empty( $redirect_url ) ) {
				wp_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * @return MC4WP_API
	 */
	protected function get_api() {
		return mc4wp('api');
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	protected function get_log() {
		return mc4wp('log');
	}

}