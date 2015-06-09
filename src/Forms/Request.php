<?php

/**
* Handles form submissions
*/
class MC4WP_Form_Request {

	/**
	 * @var MC4WP_Form
	 */
	public $form;

	/**
	 * @var array
	 */
	public $config = array();

	/**
	 * @var array
	 */
	public $data = array();

	/**
	 * Array of API requests this form is responsible for
	 *
	 * @var array
	 */
	public $requests = array();

	/**
	 * @var array Array of responses received from MailChimp
	 */
	public $responses = array();

	/**
	 * @var bool
	 */
	public $success;

	/**
	 * @var string The
	 */
	public $status = '';

	/**
	 * Constructor
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {

		// find fields prefixed with _mc4wp_
		$this->config = $this->parse_config( $data );

		// normalize user data
		$this->data = $this->normalize_data( $data );

		// get form
		$this->form = MC4WP_Form::get( $this->config['form_id'], $this );
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function parse_config( &$data ) {

		$defaults = array(
			'form_id' => 0,
			'lists' => array(),
			'action' => 'subscribe'
		);
		$config = array();

		foreach ( $data as $key => $value ) {
			if ( strpos( $key, '_mc4wp_' ) === 0 ) {

				// remove data from array
				unset( $data[ $key ] );

				$key            = substr( $key, 7 );
				$config[ $key ] = $value;
			}
		}

		return array_merge( $defaults, $config );
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
		 * @filter  `mc4wp_form_data`
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
		} elseif ( is_array( $value ) ) {
			$value = array_map( array( $this, 'sanitize_deep' ), $value );
		} elseif ( is_object( $value ) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
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

		// check required fields
		$required_fields = $this->form->get_required_fields();
		foreach( $this->data as $field => $value ) {
			// check required fields
			if( in_array( $field, $required_fields ) && empty( $value ) ) {
				$this->status = 'required_field_missing';
				return false;
			}
		}

		$validator = new MC4WP_Form_Validator( $this->config, $this->data );

		// validate nonce
		if ( ! $validator->validate_nonce() ) {
			$this->status = 'invalid_nonce';

			return false;
		}

		// ensure honeypot was given but not filled
		if ( ! $validator->validate_honeypot() ) {
			$this->status = 'spam';

			return false;
		}

		// check timestamp difference, token should be generated at least 2 seconds before form submit
		if ( ! $validator->validate_timestamp() ) {
			$this->status = 'spam';

			return false;
		}

		// check if captcha was present and valid
		if ( ! $validator->validate_captcha() ) {
			$this->status = 'invalid_captcha';

			return false;
		}

		// validate email
		if ( ! $validator->validate_email() ) {
			$this->status = 'invalid_email';

			return false;
		}

		// validate selected or submitted lists
		if ( ! $validator->validate_lists( $this->get_lists() ) ) {
			$this->status = 'no_lists_selected';

			return false;
		}

		// run custom validation (using filter)
		$custom_validation = $validator->custom_validation();
		if ( $custom_validation !== true ) {
			$this->status = $custom_validation;

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
			'{form_id}'      => $this->form->ID,
			'{form_element}' => $this->config['form_element_id'],
			'{email}'        => urlencode( $this->data['EMAIL'] )
		);
		$url = MC4WP_Tools::replace_variables( $this->form->settings['redirect'], $additional_replacements );

		return $url;
	}

	/**
	 * Send HTTP response
	 */
	public function respond() {

		do_action( 'mc4wp_form_request_respond', $this );

		// do stuff on success, non-AJAX only
		if ( $this->success ) {

			/**
			 * @action mc4wp_form_success
			 *
			 * Use to hook into successful form sign-ups
			 *
			 * @param   int    $form_id The ID of the submitted form (PRO ONLY)
			 * @param   string $email   The email of the subscriber
			 * @param   array  $data    Additional list fields, like FNAME etc (if any)
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
			 * @param   int    $form_id The ID of the submitted form (PRO ONLY)
			 * @param   string $email   The email of the subscriber
			 * @param   array  $data    Additional list fields, like FNAME etc (if any)
			 */
			do_action( 'mc4wp_form_error_' . $this->status, 0, $this->data['EMAIL'], $this->data );
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
		if ( isset( $this->config['lists'] ) && ! empty( $this->config['lists'] ) ) {

			$lists = $this->config['lists'];

			// make sure lists is an array
			if ( ! is_array( $lists ) ) {
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
	public function get_response_html() {

		// get all form messages
		$messages = $this->form->get_messages();

		// retrieve correct message
		$message = ( isset( $messages[ $this->status ] ) ) ? $messages[ $this->status ] : $messages['error'];

		// replace variables in message text
		$message['text'] = MC4WP_Tools::replace_variables( $message['text'], array(), array_values( $this->get_lists() ) );

		$html = '<div class="mc4wp-alert mc4wp-' . esc_attr( $message['type'] ) . '">' . $message['text'] . '</div>';

		// show additional MailChimp API errors to administrators
		if ( ! $this->success && current_user_can( 'manage_options' ) ) {

			if ( ! empty( $this->responses[0]->error ) ) {
				$html .= '<div class="mc4wp-alert mc4wp-error">';
				$html .= $this->responses[0]->error;
				$html .= '<br /><small>' . __( 'This message is only visible to logged-in administrators.', 'mailchimp-for-wp' ) . '</small>';
				$html .= '</div>';
			}
		}

		return $html;
	}

	/**
	 * Gets the email_type
	 *
	 * @return string The email type to use for subscription coming from this form
	 */
	protected function get_email_type( ) {

		$email_type = 'html';

		// get email type from form
		if( isset( $this->config['email_type'] ) ) {
			$email_type = sanitize_text_field( $this->config['email_type'] );
		}

		// allow plugins to override this email type
		$email_type = apply_filters( 'mc4wp_email_type', $email_type );

		return (string) $email_type;
	}

	/**
	 * Parse an array of Merge Variables from the form data
	 *
	 * @return array
	 */
	protected function parse_merge_vars() {

		// no need to parse fields when action is unsubscribe
		if( $this->config['action'] === 'unsubscribe' ) {
			return array();
		}

		$merge_vars = $this->data;

		// remove EMAIL from merge vars
		if( isset( $merge_vars['EMAIL'] ) ) {
			unset( $merge_vars['EMAIL'] );
		}

		// todo: format fields like address, birthday and date.

		// loop through list groupings if GROUPINGS data was sent
		if( isset( $merge_vars['GROUPINGS'] ) ) {

			$formatted_groupings = array();

			if( ! is_array( $merge_vars['GROUPINGS'] ) ) {
				$merge_vars['GROUPINGS'] = (array) $merge_vars['GROUPINGS'];
			}

			// loop through each grouping
			foreach( $merge_vars['GROUPINGS'] as $grouping_id_or_name => $groups ) {

				$grouping = array();

				// group ID or group name given?
				if( is_numeric( $grouping_id_or_name ) ) {
					$grouping['id'] = absint( $grouping_id_or_name );
				} else {
					$grouping['name'] = $grouping_id_or_name;
				}

				// comma separated list should become an array
				if( ! is_array( $groups ) ) {
					$groups = explode( ',', $groups );
				}

				// strip slashes in group names
				$grouping['groups'] = $groups;

				// add grouping to array
				$formatted_groupings[] = $grouping;

			} // end foreach $groupings

			// replace existing groupings with formatted groupings
			$merge_vars['GROUPINGS'] = $formatted_groupings;
		}

		// add OPTIN_IP, we do this here as the user shouldn't be allowed to set this
		$merge_vars['OPTIN_IP'] = MC4WP_Tools::get_client_ip();

		// make sure MC_LANGUAGE matches the requested format. Useful when getting the language from WPML etc.
		if( isset( $merge_vars['MC_LANGUAGE'] ) ) {
			$merge_vars['MC_LANGUAGE'] = strtolower( substr( $merge_vars['MC_LANGUAGE'], 0, 2 ) );
		}

		// allow filtering of merge vars
		/**
		 * @api
		 * @filter `mc4wp_form_merge_vars'
		 * @param array $merge_vars
		 * @param object $form
		 */
		$merge_vars = apply_filters( 'mc4wp_form_merge_vars', $merge_vars, $this );

		return $merge_vars;
	}

	/**
	 * Prepare the requests this form creates
	 *
	 * @return bool
	 */
	public function prepare() {

		$lists = $this->get_lists();
		$merge_vars = $this->parse_merge_vars();
		$email = $this->data['EMAIL'];
		$config = array(
			'email_type' => $this->get_email_type(),
			'ip' => MC4WP_Tools::get_client_ip()
		);
		$extra = array(
			'related_object_id' => $this->form->ID,
			'referer' => $_SERVER['HTTP_REFERER'],
			'type' => 'form'
		);

		// create a request object for each list
		foreach( $lists as $list_id ) {
			$request = MC4WP_API_Request::create( $this->config['action'], $list_id, $email, $merge_vars, $config, $extra );
			$this->requests[] = $request;
		}

		return true;
	}

	/**
	 * Process the prepared API requests (if any)
	 *
	 * @return bool
	 */
	public function process() {

		if( count( $this->requests ) === 0) {
			return false;
		}

		/** @var MC4WP_API_Request $request */
		foreach( $this->requests as $request ) {

			/** @var MC4WP_API_Response $response */
			$response = $request->process();
			$this->responses[] = $response;
		}

		// use status from first result
		$this->success = $this->responses[0]->success;
		$this->status = $this->responses[0]->code;

		do_action( 'mc4wp_form_request_processed', $this );

		return $this->success;
	}

}