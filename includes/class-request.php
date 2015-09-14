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
	 * @var string
	 */
	protected $message_type = '';

	/**
	 * @var bool
	 */
	public $success = false;

	/**
	 * @var array
	 */
	public $internal_data = array();

	/**
	 * @var array
	 */
	public $user_data = array();


	/**
	 * Constructor
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {

		// find fields prefixed with _mc4wp_
		$this->internal_data = $this->get_internal_data( $data );

		// normalize user data
		$this->user_data = $this->normalize_data( $data );

		// store number of submitted form
		$this->form_element_id = (string) $this->internal_data['form_element_id'];

		// get form
		$this->form = MC4WP_Form::get( $this );
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function get_internal_data( &$data ) {
		$config = array();

		foreach( $data as $key => $value ) {
			if( stripos( $key, '_mc4wp_' ) === 0 ) {

				// remove data from array
				unset( $data[$key] );

				// get part after "mc4wp_" and use that as key
				$key = substr( $key, 7 );

				// if key starts with h_, change it to say "honeypot" (because field has dynamic name attribute)
				if( strpos( $key, 'h_' ) === 0 ){
					$key = 'honeypot';
				}

				$config[ $key ] = $value;
			}
		}

		return $config;
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
		$data = $this->sanitize_deep( $data );

		/**
		 * @filter `mc4wp_form_data`
		 * @expects array
		 */
		$data = apply_filters( 'mc4wp_form_data', $data );

		return (array) $data;
	}

	/**
	 * @param $value
	 *
	 * @return array|string
	 */
	public function sanitize_deep( $value ) {

		if ( is_scalar( $value ) ) {
			$value = sanitize_text_field( $value );
		} elseif( is_array( $value ) ) {
			$value = array_map( array( $this, 'sanitize_deep' ), $value );
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = $this->sanitize_deep( $data );
			}
		}

		return $value;
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

		$validator = new MC4WP_Form_Validator( $this->internal_data, $this->user_data );

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
			'{email}' => urlencode( $this->user_data['EMAIL'] )
		);
		$url = MC4WP_Tools::replace_variables( $this->form->settings['redirect'], $additional_replacements, null, 'url' );
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
			do_action( 'mc4wp_form_success', 0, $this->user_data['EMAIL'], $this->user_data );

			// check if we want to redirect the visitor
			if ( ! empty( $this->form->settings['redirect'] ) ) {
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
			do_action( 'mc4wp_form_error_' . $this->message_type, 0, $this->user_data['EMAIL'], $this->user_data );
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
		if( isset( $this->internal_data['lists'] ) && ! empty( $this->internal_data['lists'] ) ) {

			$lists = $this->internal_data['lists'];

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

}