<?php
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* Handles form submissions
*/
class MC4WP_Lite_Form_Request {


	/**
	 * @var array The form options
	 */
	private $form_options;

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
	 * @var string
	 */
	private $error_code = 'error';

	/**
	 * @var string
	 */
	private $mailchimp_error = '';

	/**
	 * @var MC4WP_Form
	 */
	private $form;

	/**
	 * @var string
	 */
	public $form_element_id = '';

	/**
	 * @var array
	 */
	public $data = array();

	/**
	 * @var bool
	 */
	public $success = false;

	/**
	 * @var bool
	 */
	public $ready = false;


	/**
	 * Constructor
	 *
	 * @param array $form_data
	 */
	public function __construct( $form_data ) {

		$this->data = $this->normalize_form_data( $form_data );

		// store number of submitted form
		$this->form_element_id = (string) $this->data['_MC4WP_FORM_ELEMENT_ID'];
		$this->form_options = mc4wp_get_options( 'form' );
		$this->form = MC4WP_Form::get( $this );
	}

	/**
	 * Prepare data for MailChimp API request
	 */
	public function prepare() {
		$this->guess_fields();
		$this->map_data();
	}

	/**
	 * Try to guess the values of various fields, if not given.
	 */
	protected function guess_fields() {
		// add some data to the posted data, like FNAME and LNAME
		$this->data = MC4WP_Tools::guess_merge_vars( $this->data );
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function normalize_form_data( array $data ) {

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
			$this->error_code = 'invalid_nonce';
			return false;
		}

		// ensure honeypot was given but not filled
		if( ! $validator->validate_honeypot() ) {
			$this->error_code = 'spam';
			return false;
		}

		// check timestamp difference, token should be generated at least 2 seconds before form submit
		if( ! $validator->validate_timestamp() ) {
			$this->error_code = 'spam';
			return false;
		}

		// check if captcha was present and valid
		if( ! $validator->validate_timestamp() ) {
			$this->error_code = 'invalid_captcha';
			return false;
		}

		// validate email
		if( ! $validator->validate_email() ) {
			$this->error_code = 'invalid_email';
			return false;
		}

		// validate selected or submitted lists
		if( ! $validator->validate_lists( $this->get_lists() ) ) {
			$this->error_code = 'no_lists_selected';
			return false;
		}

		// run custom validation (using filter)
		$custom_validation = $validator->custom_validation();
		if( $custom_validation !== true ) {
			$this->error_code = $custom_validation;
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

		$needles = array(
			'{form_id}',
			'{email}',
		);
		$replacements = array(
			$this->data['_MC4WP_FORM_ID'],
			$this->data['EMAIL'],
		);
		$url = str_ireplace( $needles, $replacements, $this->form_options['redirect'] );

		return $url;
	}

	/**
	 * Send HTTP response
	 */
	public function send_http_response() {

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
			do_action( 'mc4wp_form_error_' . $this->error_code, 0, $this->data['EMAIL'], $this->data );
		}

	}

	/**
	 * Maps the received data to MailChimp lists
	 *
	 * @return array
	 */
	protected function map_data() {

		$mapper = new MC4WP_Field_Mapper( $this->data, $this->get_lists() );

		if( $mapper->success ) {
			$this->list_fields_map = $mapper->get_list_fields_map();
			$this->global_fields = $mapper->get_global_fields();
			$this->unmapped_fields = $mapper->get_unmapped_fields();
		} else {
			$this->error_code = $mapper->get_error_code();
		}

		$this->ready = $mapper->success;
		return $mapper->success;
	}
	/**
	 * Subscribes the given email and additional list fields
	 * @return bool
	 */
	public function subscribe() {

		$api = mc4wp_get_api();

		do_action( 'mc4wp_before_subscribe', $this->data['EMAIL'], $this->data, 0 );

		$result = false;
		$email_type = $this->get_email_type();

		// loop through selected lists
		foreach ( $this->list_fields_map as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = $this->get_list_merge_vars( $list_id, $list_field_data );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $this->data['EMAIL'], $list_merge_vars, $email_type, $this->form->settings['double_optin'], $this->form->settings['update_existing'], $this->form->settings['replace_interests'], $this->form->settings['send_welcome'] );
			do_action( 'mc4wp_subscribe', $this->data['EMAIL'], $list_id, $list_merge_vars, $result, 'form', 'form', 0 );
		}

		do_action( 'mc4wp_after_subscribe', $this->data['EMAIL'], $this->data, 0, $result );

		if ( $result !== true ) {
			// subscribe request failed, store error.
			$this->success = false;
			$this->error_code = $result;
			$this->mailchimp_error = $api->get_error_message();
			return false;
		}

		// subscription succeeded

		// store user email in a cookie
		$this->set_email_cookie( $this->data['EMAIL'] );

		// Store success result
		$this->success = true;

		return true;
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
		$merge_vars['OPTIN_IP'] = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

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
		if( isset( $this->data['_MC4WP_EMAIL_TYPE'] ) ) {
			$email_type = sanitize_text_field( $this->data['_MC4WP_EMAIL_TYPE'] );
		}

		// allow plugins to override this email type
		$email_type = apply_filters( 'mc4wp_email_type', $email_type );

		return (string) $email_type;
	}

	/**
	 * Get MailChimp List(s) to subscribe to
	 *
	 * @return array Array of selected MailChimp lists
	 */
	protected function get_lists() {

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
	 * Stores the given email in a cookie for 30 days
	 *
	 * @param string $email
	 */
	protected function set_email_cookie( $email ) {

		/**
		 * @filter `mc4wp_cookie_expiration_time`
		 * @expects timestamp
		 * @default timestamp for 30 days from now
		 *
		 * Timestamp indicating when the email cookie expires, defaults to 30 days
		 */
		$expiration_time = apply_filters( 'mc4wp_cookie_expiration_time', strtotime( '+30 days' ) );

		setcookie( 'mc4wp_email', $email, $expiration_time, '/' );
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
		$type = ( $this->success ) ? 'success' : $this->error_code;
		$message = ( isset( $messages[ $type ] ) ) ? $messages[ $type ] : $messages['error'];

		/**
		 * @filter mc4wp_form_error_message
		 * @deprecated 2.0.5
		 * @use mc4wp_form_messages
		 *
		 * Used to alter the error message, don't use. Use `mc4wp_form_messages` instead.
		 */
		$message['text'] = apply_filters( 'mc4wp_form_error_message', $message['text'], $this->error_code );

		$html = '<div class="mc4wp-alert mc4wp-'. $message['type'].'">' . $message['text'] . '</div>';

		// show additional MailChimp API errors to administrators
		if( ! $this->success && current_user_can( 'manage_options' ) ) {

			if( '' !== $this->mailchimp_error ) {
				$html .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $this->mailchimp_error . '</div>';
			}
		}

		return $html;
	}

}