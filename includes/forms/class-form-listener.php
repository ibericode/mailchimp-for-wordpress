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
	 * @param MC4WP_Request $request
	 */
	public function process_subscribe_form( MC4WP_Form $form, MC4WP_Request $request ) {
		$result = false;
		$mailchimp = new MC4WP_MailChimp();
		$email_type = $form->get_email_type();
		$data = $form->get_data();
		$client_ip = $request->get_client_ip();

		/** @var MC4WP_MailChimp_Subscriber $subscriber */
		$subscriber = null;

		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = apply_filters( 'mc4wp_merge_vars', $data );
		
		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = (array) apply_filters( 'mc4wp_form_merge_vars', $data, $form );

		// create a map of all lists with list-specific data
		$mapper = new MC4WP_List_Data_Mapper( $data, $form->get_lists() );

		/** @var MC4WP_MailChimp_Subscriber[] $map */
		$map = $mapper->map();

		// loop through lists
		foreach( $map as $list_id => $subscriber ) {

			$subscriber->status = $form->settings['double_optin'] ? 'pending' : 'subscribed';
			$subscriber->email_type = $email_type;
			$subscriber->ip_signup = $client_ip;

			/**
			 * Filters subscriber data before it is sent to MailChimp. Fires for both form & integration requests.
			 *
			 * @param MC4WP_MailChimp_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'mc4wp_subscriber_data', $subscriber );

			/**
			 * Filters subscriber data before it is sent to MailChimp. Only fires for form requests.
			 *
			 * @param MC4WP_MailChimp_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'mc4wp_form_subscriber_data', $subscriber );

			// send a subscribe request to MailChimp for each list
			$result = $mailchimp->list_subscribe( $list_id, $subscriber->email_address, $subscriber->to_array(), $form->settings['update_existing'], $form->settings['replace_interests'] );
		}

		$log = $this->get_log();

		// do stuff on failure
		if( ! is_object( $result ) || empty( $result->id ) ) {
			
			if( $mailchimp->get_error_code() == 214 ) {
				$form->add_error('already_subscribed');
				$log->warning( sprintf( "Form %d > %s is already subscribed to the selected list(s)", $form->ID, mc4wp_obfuscate_string( $data['EMAIL'] ) ) );
			} else {
				$form->add_error('error');
				$log->error( sprintf( 'Form %d > MailChimp API error: %s %s', $form->ID, $mailchimp->get_error_code(), $mailchimp->get_error_message() ) );
			}

			// bail
			return;
		}

		// Success! Did we update or newly subscribe?
		if( $result->status === 'subscribed' && $result->was_already_on_list ) {
			$form->add_message('updated');
		} else {
			$form->add_message('subscribed');
		}

		$log->info( sprintf( "Form %d > Successfully subscribed %s", $form->ID, $data['EMAIL'] ) );

		/**
		 * Fires right after a form was used to subscribe.
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Form $form Instance of the submitted form
		 * @param string $email
		 * @param array $data
		 * @param MC4WP_MailChimp_Subscriber[] $subscriber
		 */
		do_action( 'mc4wp_form_subscribed', $form, $subscriber->email_address, $data, $map );
	}

	/**
	 * @param MC4WP_Form $form
	 * @param MC4WP_Request $request
	 */
	public function process_unsubscribe_form( MC4WP_Form $form, MC4WP_Request $request = null ) {

		$mailchimp = new MC4WP_MailChimp();
		$log = $this->get_log();
		$result = null;
        $data = $form->get_data();

        // unsubscribe from each list
		foreach( $form->get_lists() as $list_id ) {
            // TODO: Check if on list before proceeding with unsubscribe call?
			$result = $mailchimp->list_unsubscribe( $list_id, $data['EMAIL'] );
		}

		if( ! $result ) {
            $form->add_error( 'error' );
            $log->error( sprintf( 'Form %d > MailChimp API error: %s', $form->ID, $mailchimp->get_error_message() ) );

			// bail
			return;
		}

		// Success! Unsubscribed.
        $form->add_message('unsubscribed');
        $log->info( sprintf( "Form %d > Successfully unsubscribed %s", $form->ID, $data['EMAIL'] ) );


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