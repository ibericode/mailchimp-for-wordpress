<?php

/**
* Handles form submissions
*/
abstract class MC4WP_Request implements iMC4WP_Request {

	/**
	 * @var string
	 */
	protected $mailchimp_error = '';

	/**
	 * @var MC4WP_Form
	 */
	protected $form;

	/**
	 * @var string
	 */
	public $form_element_id = '';

	/**
	 * @var array
	 */
	public $data = array();

	/**
	 * @var string
	 */
	protected $message_type = '';

	/**
	 * @var bool
	 */
	public $success = true;


	/**
	 * Constructor
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {

		$this->data = $this->normalize_data( $data );

		// store number of submitted form
		$this->form_element_id = (string) $this->data['_MC4WP_FORM_ELEMENT_ID'];
		$this->form = MC4WP_Form::get( $this );
	}


	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function normalize_data( array $data ) {

		// uppercase all data keys
		$data = array_change_key_case( $data, CASE_UPPER );

		// strip slashes on everything
		$data = stripslashes_deep( $data );

		// sanitize all scalar values
		foreach( $data as $key => $value ) {
			if( is_scalar( $value ) ) {
				$data[ $key ] = sanitize_text_field( $value );
			}
		}

		/**
		 * @filter `mc4wp_form_data`
		 * @expects array
		 */
		$data = apply_filters( 'mc4wp_form_data', $data );

		return (array) $data;
	}

	/**
	 * Validates the request
	 *
	 * - Nonce validity
	 * - Honeypot
	 * - Captcha
	 * - Email address
	 * - Lists (POST and options)
	 * - Additional validation using a filter.
	 *
	 * @return bool
	 */
	public function validate() {

		$validator = new MC4WP_Form_Validator( $this->data );

		// validate nonce
		if( ! $validator->validate_nonce() ) {
			$this->message_type = 'invalid_nonce';
			return false;
		}

		// ensure honeypot was given but not filled
		if( ! $validator->validate_honeypot() ) {
			$this->message_type = 'spam';
			return false;
		}

		// check timestamp difference, token should be generated at least 2 seconds before form submit
		if( ! $validator->validate_timestamp() ) {
			$this->message_type = 'spam';
			return false;
		}

		// check if captcha was present and valid
		if( ! $validator->validate_captcha() ) {
			$this->message_type = 'invalid_captcha';
			return false;
		}

		// validate email
		if( ! $validator->validate_email() ) {
			$this->message_type = 'invalid_email';
			return false;
		}

		// validate selected or submitted lists
		if( ! $validator->validate_lists( $this->get_lists() ) ) {
			$this->message_type = 'no_lists_selected';
			return false;
		}

		// run custom validation (using filter)
		$custom_validation = $validator->custom_validation();
		if( $custom_validation !== true ) {
			$this->message_type = $custom_validation;
			return false;
		}

		// finally, return true
		return true;
	}

	/**
	 * Get the final Redirect URL with replaced variables
	 *
	 * @return string
	 */
	protected function get_redirect_url() {
		$additional_replacements = array(
			'{form_id}' => $this->form->ID,
			'{form_element}' => $this->form_element_id,
			'{email}' => urlencode( $this->data['EMAIL'] )
		);
		$url = MC4WP_Tools::replace_variables( $this->form->settings['redirect'], $additional_replacements );
		return $url;
	}

	/**
	 * Send HTTP response
	 */
	public function respond() {

		// do stuff on success, non-AJAX only
		if( $this->success ) {

			/**
			 * @action mc4wp_form_success
			 *
			 * Use to hook into successful form sign-ups
			 *
			 * @param   int     $form_id        The ID of the submitted form (PRO ONLY)
			 * @param   string  $email          The email of the subscriber
			 * @param   array   $data           Additional list fields, like FNAME etc (if any)
			 */
			do_action( 'mc4wp_form_success', 0, $this->data['EMAIL'], $this->data );

			// check if we want to redirect the visitor
			if ( '' !== $this->form->settings['redirect'] ) {
				wp_redirect( $this->get_redirect_url() );
				exit;
			}

		} else {

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
			do_action( 'mc4wp_form_error_' . $this->message_type, 0, $this->data['EMAIL'], $this->data );
		}

	}


	/**
	 * Get MailChimp List(s) to subscribe to
	 *
	 * @return array Array of selected MailChimp lists
	 */
	public function get_lists() {

		$lists = $this->form->settings['lists'];

		// get lists from form, if set.
		if( isset( $this->data['_MC4WP_LISTS'] ) && ! empty( $this->data['_MC4WP_LISTS'] ) ) {

			$lists = $this->data['_MC4WP_LISTS'];

			// make sure lists is an array
			if( ! is_array( $lists ) ) {
				$lists = sanitize_text_field( $lists );
				$lists = array( $lists );
			}

		}

		// allow plugins to alter the lists to subscribe to
		$lists = apply_filters( 'mc4wp_lists', $lists );

		return (array) $lists;
	}

	/**
	 * Returns the HTML for success or error messages
	 *
	 * @return string
	 */
	public function get_response_html( ) {

		// get all form messages
		$messages = $this->form->get_messages();

		// retrieve correct message
		$message = ( isset( $messages[ $this->message_type ] ) ) ? $messages[ $this->message_type ] : $messages['error'];

		// replace variables in message text
		$message['text'] = MC4WP_Tools::replace_variables( $message['text'], array(), array_values( $this->get_lists() ) );

		$html = '<div class="mc4wp-alert mc4wp-' . esc_attr( $message['type'] ) . '">' . $message['text'] . '</div>';

		// show additional MailChimp API errors to administrators
		if( ! $this->success && current_user_can( 'manage_options' ) ) {

			if( '' !== $this->mailchimp_error ) {
				$html .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $this->mailchimp_error . '</div>';
			}
		}

		return $html;
	}

	/**
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

}