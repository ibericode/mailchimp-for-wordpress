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
			$form = mc4wp_get_form( $request->params->get( '_mc4wp_form_id' ) );
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
			call_user_func( array( $this, $method ), $form, $request );
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
	 * @Param MC4WP_Request $request
	 */
	public function process_subscribe_form( MC4WP_Form $form, MC4WP_Request $request ) {
		$result = false;
		$mailchimp = new MC4WP_MailChimp();
		$email_type = $form->get_email_type();
		$data = $form->data;
		$client_ip = $request->get_client_ip();
		$subscriber_data = null;
		
		/**
		 * TODO: Deprecate this filter in favor of clearer ones.
		 *
		 * Filters merge vars which are sent to MailChimp, only fires for form requests.
		 *
		 * @param array $data
		 * @param MC4WP_Form $form
		 */
		$data = (array) apply_filters( 'mc4wp_form_merge_vars', $data, $form );

		// create a map of all lists with list-specific data
		$mapper = new MC4WP_List_Data_Mapper( $data, $form->get_lists() );

		/** @var MC4WP_MailChimp_Subscriber_Data[] $map */
		$map = $mapper->map();

		// loop through lists
		foreach( $map as $list_id => $subscriber_data ) {

			$subscriber_data->status = $form->settings['double_optin'] ? 'pending' : 'subscribed';
			$subscriber_data->email_type = $email_type;
			$subscriber_data->ip_opt = $client_ip;

			// send a subscribe request to MailChimp for each list
			$result = $mailchimp->list_subscribe( $list_id, $subscriber_data->email_address, $subscriber_data->to_array(), $form->settings['update_existing'], $form->settings['replace_interests'] );
		}

		$log = $this->get_log();

		// do stuff on failure
		if( ! is_object( $result ) || empty( $result->id ) ) {
			
			if( $mailchimp->get_error_code() == 214 ) {
				$form->add_error('already_subscribed');
				$log->warning( sprintf( "Form %d > %s is already subscribed to the selected list(s)", $form->ID, mc4wp_obfuscate_string( $form->data['EMAIL'] ) ) );
			} else {
				$form->add_error('error');
				$log->error( sprintf( 'Form %d > MailChimp API error: %s', $form->ID, $mailchimp->get_error_message() ) );
			}

			// bail
			return;
		}

		// Success! Did we update or newly subscribe?
		if( $result->status === 'subscribed' && $result->timestamp_signup < $result->last_changed ) {
			$form->add_message('updated');
		} else {
			$form->add_message('subscribed');
		}

		$log->info( sprintf( "Form %d > Successfully subscribed %s", $form->ID, $form->data['EMAIL'] ) );

		/**
		 * Fires right after a form was used to subscribe.
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Form $form Instance of the submitted form
		 * @param string $email
		 * @param array $data
		 * @param MC4WP_MailChimp_Subscriber_Data $subscriber_data
		 */
		do_action( 'mc4wp_form_subscribed', $form, $subscriber_data->email_address, $data, $subscriber_data );
	}

	/**
	 * @param MC4WP_Form $form
	 * @param MC4WP_Request $request
	 */
	public function process_unsubscribe_form( MC4WP_Form $form, MC4WP_Request $request = null ) {

		$mailchimp = new MC4WP_MailChimp();
		$log = $this->get_log();
		$result = null;

		foreach( $form->get_lists() as $list_id ) {
			$result = $mailchimp->list_unsubscribe( $list_id, $form->data['EMAIL'] );
		}

		if( ! $result ) {
			// not subscribed is a soft-error
			if( in_array( $mailchimp->get_error_code(), array( 215, 232 ) ) ) {
				$form->add_error( 'not_subscribed' );
				$log->info( sprintf( 'Form %d > %s is not subscribed to the selected list(s)', $form->ID, $form->data['EMAIL'] ) );
			} else {
				$form->add_error( 'error' );
				$log->error( sprintf( 'Form %d > MailChimp API error: %s', $form->ID, $mailchimp->get_error_message() ) );
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
	 * @return MC4WP_API_v3
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