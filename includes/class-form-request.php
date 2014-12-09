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
	 * @var int
	 */
	private $form_instance_number = 0;

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @var bool Guilty until proven otherwise.
	 */
	private $success = false;

	/**
	 * @var bool Guilty until proven otherwise.
	 */
	private $is_valid = false;

	/**
	 * @var array The form options
	 */
	private $form_options;

	/**
	 * @var array
	 */
	private $lists_fields_map = array();

	/**
	 * @var array
	 */
	private $unmapped_fields = array();

	/**
	 * @var string
	 */
	private $error_code = 'error';

	/**
	 * @var string
	 */
	private $mailchimp_error = '';

	/**
	 * @return bool
	 */
	public function is_successful() {
		return $this->success;
	}

	/**
	 * @return int
	 */
	public function get_form_instance_number() {
		return $this->form_instance_number;
	}

	/**
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		// uppercase all POST keys
		$this->data = array_change_key_case( $_POST, CASE_UPPER );

		// store number of submitted form
		$this->form_instance_number = absint( $this->data['_MC4WP_FORM_INSTANCE'] );
		$this->form_options = mc4wp_get_options( 'form' );

		$this->is_valid = $this->validate();

		// normalize posted data
		$this->data = $this->sanitize();

		if( $this->is_valid ) {

			// add some data to the posted data, like FNAME and LNAME
			$this->guess_missing_fields( $this->data );

			// map fields to corresponding MailChimp lists
			if( $this->map_data() ) {

				// subscribe using the processed data
				$this->success = $this->subscribe( $this->lists_fields_map );
			}
		}

		// send HTTP response
		$this->send_http_response();

		return $this->success;
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
	private function validate() {

		// detect caching plugin
		$using_caching = ( defined( 'WP_CACHE' ) && WP_CACHE );

		// validate form nonce, but only if not using caching
		if ( ! $using_caching && ( ! isset( $this->data['_MC4WP_FORM_NONCE'] ) || ! wp_verify_nonce( $this->data['_MC4WP_FORM_NONCE'], '_mc4wp_form_nonce' ) ) ) {
			$this->error_code = 'invalid_nonce';
			return false;
		}

		// ensure honeypot was not filed
		if ( isset( $this->data['_MC4WP_REQUIRED_BUT_NOT_REALLY'] ) && ! empty( $this->data['_MC4WP_REQUIRED_BUT_NOT_REALLY'] ) ) {
			$this->error_code = 'spam';
			return false;
		}

		// check if captcha was present and valid
		if( isset( $this->data['_MC4WP_HAS_CAPTCHA'] ) && $this->data['_MC4WP_HAS_CAPTCHA'] == 1 && function_exists( 'cptch_check_custom_form' ) && cptch_check_custom_form() !== true ) {
			$this->error_code = 'invalid_captcha';
			return false;
		}

		// validate email
		if( ! isset( $this->data['EMAIL'] ) || ! is_string( $this->data['EMAIL'] ) || ! is_email( $this->data['EMAIL'] ) ) {
			$this->error_code = 'invalid_email';
			return false;
		}

		// get lists to subscribe to
		$lists = $this->get_lists();

		if ( empty( $lists ) ) {
			$this->error_code = 'no_lists_selected';
			return false;
		}

		/**
		 * @filter mc4wp_valid_form_request
		 *
		 * Use this to perform custom form validation.
		 * Return true if the form is valid or an error string if it isn't.
		 * Use the `mc4wp_form_messages` filter to register custom error messages.
		 */
		$valid_form_request = apply_filters( 'mc4wp_valid_form_request', true, $this->data );
		if( $valid_form_request !== true ) {
			$this->error_code = $valid_form_request;
			return false;
		}

		return true;
	}

	/**
	 * Sanitize the request data.
	 *
	 * - Strips internal variables
	 * - Strip ignored fields
	 * - Sanitize scalar values
	 * - Strip slashes on everything
	 *
	 * @return array
	 */
	private function sanitize() {
		$data = array();

		// Ignore those fields, we don't need them
		$ignored_fields = array( 'CPTCH_NUMBER', 'CNTCTFRM_CONTACT_ACTION', 'CPTCH_RESULT', 'CPTCH_TIME' );

		foreach( $this->data as $key => $value ) {

			// Sanitize key
			$key = trim( $key );

			// Skip field if it starts with _ or if it's in ignored_fields array
			if( $key[0] === '_' || in_array( $key, $ignored_fields ) ) {
				continue;
			}

			// Sanitize value
			$value = ( is_scalar( $value ) ) ? sanitize_text_field( $value ) : $value;

			// Add value to array
			$data[ $key ] = $value;
		}

		// strip slashes on everything
		$data = stripslashes_deep( $data );

		// store data somewhere safe
		return $data;
	}

	/**
	 * Guesses the value of some fields.
	 *
	 * - FNAME and LNAME, if NAME is given
	 *
	 * @param array $data
	 * @return array
	 */
	public function guess_missing_fields( $data ) {

		// fill FNAME and LNAME if they're not set, but NAME is.
		if( isset( $data['NAME'] ) && ! isset( $data['FNAME'] ) && ! isset( $data['LNAME'] ) ) {

			$strpos = strpos( $data['NAME'], ' ' );
			if( $strpos !== false ) {
				$data['FNAME'] = substr( $data['NAME'], 0, $strpos );
				$data['LNAME'] = substr( $data['NAME'], $strpos );
			} else {
				$data['FNAME'] = $data['NAME'];
			}
		}

		return $data;
	}

	/**
	 * Send HTTP response
	 */
	public function send_http_response() {

		// do stuff on success, non-AJAX only
		if( $this->success ) {

			// check if we want to redirect the visitor
			if ( ! empty( $this->form_options['redirect'] ) ) {

				$redirect_url = add_query_arg( array( 'mc4wp_email' => urlencode( $this->data['EMAIL'] ) ), $this->form_options['redirect'] );
				wp_redirect( $redirect_url );
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
	private function map_data() {

		$data = $this->data;

		$map = array();
		$mapped_fields = array( 'EMAIL' );
		$unmapped_fields = array();

		$mailchimp = new MC4WP_MailChimp();

		// loop through selected lists
		foreach( $this->get_lists() as $list_id ) {

			$list = $mailchimp->get_list( $list_id, false, true );

			// skip this list if it's unexisting
			if( ! is_object( $list ) || ! isset( $list->merge_vars ) ) {
				continue;
			}

			// start with empty list map
			$list_map = array();

			// loop through other list fields
			foreach( $list->merge_vars as $field ) {

				// skip EMAIL field
				if( $field->tag === 'EMAIL' ) {
					continue;
				}

				// check if field is required
				if( $field->req ) {
					if( ! isset( $data[ $field->tag ] ) || '' === $data[ $field->tag ] ) {
						$this->error_code = 'required_field_missing';
						return false;
					}
				}

				// if field is not set, continue.
				if( ! isset( $data[ $field->tag ] ) ) {
					continue;
				}

				// grab field value from data
				$field_value = $data[ $field->tag ];

				$field_value = $this->format_field_value( $field_value, $field->field_type );

				// add field value to map
				$mapped_fields[] = $field->tag;
				$list_map[ $field->tag ] = $field_value;
			}

			// loop through list groupings if GROUPINGS data was sent
			if( isset( $data['GROUPINGS'] ) && is_array( $data['GROUPINGS'] ) && ! empty( $list->interest_groupings ) ) {

				$list_map['GROUPINGS'] = array();

				foreach( $list->interest_groupings as $grouping ) {

					// check if data for this group was sent
					if( isset( $data['GROUPINGS'][$grouping->id] ) ) {
						$group_data = $data['GROUPINGS'][$grouping->id];
					} elseif( isset( $data['GROUPINGS'][$grouping->name] ) ) {
						$group_data = $data['GROUPINGS'][$grouping->name];
					} else {
						// no data for this grouping was sent, just continue.
						continue;
					}

					// format new grouping
					$grouping = array(
						'id' => $grouping->id,
						'groups' => $group_data
					);

					// make sure groups is an array
					if( ! is_array( $grouping['groups'] ) ) {
						$grouping['groups'] = sanitize_text_field( $grouping['groups'] );
						$grouping['groups'] = explode( ',', $grouping['groups'] );
					}

					$list_map['GROUPINGS'][] = $grouping;

				}

				// unset GROUPINGS if no grouping data was found for this list
				if( 0 === count( $list_map['GROUPINGS'] ) ) {
					unset( $list_map['GROUPINGS'] );
				}
			}

			// add to total map
			$map[ $list_id ] = $list_map;


		}

		// loop through data to find unmapped fields
		if( count( $mapped_fields ) < count( $data ) ) {
			foreach( $data as $field_key => $field_value ) {
				if( ! in_array( $field_key, $mapped_fields ) ) {
					$unmapped_fields[ $field_key ] = $field_value;
				}
			}
		}

		$this->unmapped_fields = $unmapped_fields;
		$this->lists_fields_map = $map;
		return true;
	}

	/**
	 * Subscribes the given email and additional list fields
	 *
	 * @param array $lists_data
	 * @return bool
	 */
	private function subscribe( $lists_data ) {

		$api = mc4wp_get_api();

		do_action( 'mc4wp_before_subscribe', $this->data['EMAIL'], $this->data, 0 );

		$result = false;
		$email_type = $this->get_email_type();

		foreach ( $lists_data as $list_id => $list_field_data ) {

			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = $this->get_list_merge_vars( $list_field_data );
			$list_merge_vars = apply_filters( 'mc4wp_merge_vars', $list_merge_vars, 0, $list_id );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $this->data['EMAIL'], $list_merge_vars, $email_type, $this->form_options['double_optin'], $this->form_options['update_existing'], $this->form_options['replace_interests'], $this->form_options['send_welcome'] );
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

		/**
		 * @deprecated Don't use, will be removed in v2.0
		 * TODO: remove this
		 */
		$from_url = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : '';
		do_action( 'mc4wp_subscribe_form', $this->data['EMAIL'], array_keys( $lists_data ), 0, $this->data, $from_url );

		// Store success result
		$this->success = true;

		return true;
	}

	/**
	 * Format field value according to its type
	 *
	 * @param $field_type
	 * @param $field_value
	 *
	 * @return array|string
	 */
	private function format_field_value( $field_value, $field_type ) {

		$field_type = strtolower( $field_type );

		switch( $field_type ) {

			// birthday fields need to be MM/DD for the MailChimp API
			case 'birthday':
				$field_value = (string) date( 'm/d', strtotime( $field_value ) );
				break;

			case 'address':

				// auto-format if addr1 is not set
				if( ! isset( $field_value['addr1'] ) ) {

					// addr1, addr2, city, state, zip, country
					$address_pieces = explode( ',', $field_value );

					// try to fill it.... this is a long shot
					$field_value = array(
						'addr1' => $address_pieces[0],
						'city'  => ( isset( $address_pieces[1] ) ) ?   $address_pieces[1] : '',
						'state' => ( isset( $address_pieces[2] ) ) ?   $address_pieces[2] : '',
						'zip'   => ( isset( $address_pieces[3] ) ) ?   $address_pieces[3] : ''
					);

				}

				break;
		}

		/**
		 * @filter `mc4wp_format_field_value`
		 * @param mixed $field_value
		 * @param string $field_type
		 * @expects mixed
		 *
		 *          Format a field value according to its MailChimp field type
		 */
		$field_value = apply_filters( 'mc4wp_format_field_value', $field_value, $field_type );

		return $field_value;
	}


	/**
	 * Adds global fields like OPTIN_IP, MC_LANGUAGE, OPTIN_DATE, etc to the list of user-submitted field data.
	 *
	 * @param $field_data
	 * @return array
	 */
	private function get_list_merge_vars( $field_data ) {

		$merge_vars = array(
			'OPTIN_IP' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] )
		);

		// add MC_LANGUAGE
		if( isset( $field_data['MC_LANGUAGE'] ) ) {
			$field_data['MC_LANGUAGE'] = strtolower( substr( $field_data['MC_LANGUAGE'], 0, 2 ) );
		}

		$merge_vars = array_merge( $merge_vars, $field_data );
		return $merge_vars;
	}

	/**
	 * Gets the email_type
	 *
	 * @return string The email type to use for subscription coming from this form
	 */
	private function get_email_type( ) {

		$email_type = 'html';

		// get email type from form
		if( isset( $_POST['_mc4wp_email_type'] ) ) {
			$email_type = sanitize_text_field( $_POST['_mc4wp_email_type'] );
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
	private function get_lists() {

		$lists = $this->form_options['lists'];

		// get lists from form, if set.
		if( isset( $_POST['_mc4wp_lists'] ) && ! empty( $_POST['_mc4wp_lists'] ) ) {

			$lists = $_POST['_mc4wp_lists'];

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
	private function set_email_cookie( $email ) {

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
		$messages = $this->get_form_messages();

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
		$message['text'] = apply_filters('mc4wp_form_error_message', $message['text'], $this->error_code );

		$html = '<div class="mc4wp-alert mc4wp-'. $message['type'].'">' . $message['text'] . '</div>';

		// show additional MailChimp API errors to administrators
		if( ! $this->success && current_user_can( 'manage_options' ) ) {

			if( '' !== $this->mailchimp_error ) {
				$html .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $this->mailchimp_error . '</div>';
			}
		}

		return $html;
	}

	/**
	 * Returns the various error and success messages in array format
	 *
	 * Example:
	 * array(
	 *      'invalid_email' => array(
	 *          'type' => 'css-class',
	 *          'text' => 'Message text'
	 *      ),
	 *      ...
	 * );
	 *
	 * @return array
	 */
	public function get_form_messages() {

		$messages = array(
			'already_subscribed' => array(
				'type' => 'notice',
				'text' => $this->form_options['text_already_subscribed']
			),
			'error' => array(
				'type' => 'error',
				'text' => $this->form_options['text_error']
			),
			'invalid_email' => array(
				'type' => 'error',
				'text' => $this->form_options['text_invalid_email']
			),
			'success' => array(
				'type' => 'success',
				'text' => $this->form_options['text_success']
			),
			'invalid_captcha' => array(
				'type' => 'error',
				'text' => $this->form_options['text_invalid_captcha']
			),
			'required_field_missing' => array(
				'type' => 'error',
				'text' => $this->form_options['text_required_field_missing']
			),
			'no_lists_selected' => array(
				'type' => 'error',
				'text' => __( 'Please select at least one list to subscribe to.', 'mailchimp-for-wp' )
			)
		);

		/**
		 * @filter mc4wp_form_messages
		 * @expects array
		 *
		 * Allows registering custom form messages, useful if you're using custom validation using the `mc4wp_valid_form_request` filter.
		 */
		$messages = apply_filters( 'mc4wp_form_messages', $messages, 0 );

		return (array) $messages;
	}


}