<?php

/**
 * Class MC4WP_Form
 *
 * Represents a Form object.
 *
 * To get a form instance, use `mc4wp_get_form( $id );` where `$id` is the post ID.
 *
 * @author Danny van Kooten
 * @package MailChimp for WordPress
 * @api
 * @since 3.0
 * @access public
 */
class MC4WP_Form {

	/**
	 * @var array Array of instantiated form objects.
	 */
	public static $instances = array();


	/**
	 * Get a shared form instance.
	 *
	 * @param int $form_id
	 * @return MC4WP_Form
	 * @throws Exception
	 */
	public static function get_instance( $form_id = 0 ) {

		if( $form_id instanceof WP_Post ) {
			$form_id = $form_id->ID;
		} else {
			$form_id = (int) $form_id;

			if( empty( $form_id ) ) {
				$form_id = (int) get_option( 'mc4wp_default_form_id', 0 );
			}
		}

		if( isset( self::$instances[ $form_id ] ) ) {
			return self::$instances[ $form_id ];
		}

		$form = new MC4WP_Form( $form_id );

		self::$instances[ $form_id ] = $form;

		return $form;
	}

	/**
	 * @var int The form ID, matches the underlying post its ID
	 */
	public $ID = 0;

	/**
	 * @var string The form name
	 */
	public $name = 'Default Form';

	/**
	 * @var string The form HTML content
	 */
	public $content = '';

	/**
	 * @var array Array of settings
	 */
	public $settings = array();

	/**
	 * @var array Array of messages
	 */
	public $messages = array();

	/**
	 * @var WP_Post The internal post object that represents this form.
	 */
	public $post;

	/**
	 * @var array Array of error code's
	 * @todo Change to actual error messages?
	 */
	public $errors = array();

	/**
	 * @var bool Was this form submitted?
	 */
	public $is_submitted = false;

	/**
	 * @var array Array of the data that was submitted, in name => value pairs.
	 *
	 * Keys in this array are uppercased and keys starting with _ are stripped.
	 */
	public $data = array();

	/**
	 * @var array Array of the raw form data that was submitted.
	 */
	public $raw_data = array();

	/**
	 * @var array
	 */
	public $config = array(
		'action' => 'subscribe',
		'lists' => array(),
		'email_type' => 'html'
	);

	/**
	 * @param int $id The post ID
	 * @throws Exception
	 */
	public function __construct( $id ) {
		$id = (int) $id;
		$this->post = $post = get_post( $id );

		if( ! is_object( $post ) || ! isset( $post->post_type ) || $post->post_type !== 'mc4wp-form' ) {
			$message = sprintf( __( 'There is no form with ID %d, perhaps it was deleted?', 'mailchimp-for-wp' ), $id );
			throw new Exception( $message );
		}

		$this->ID = $id;
		$this->name = $post->post_title;
		$this->content = $post->post_content;
		$this->settings = $this->load_settings();
		$this->messages = $this->load_messages();

		// update config from settings
		$this->config['lists'] = $this->settings['lists'];
	}


	/**
	 * Gets the form response string
	 *
	 * @return string
	 */
	public function get_response_html() {

		$html = '';
		$form = $this;

		if( $this->is_submitted ) {
			if( $this->has_errors() ) {

				// create html string of all errors
				foreach( $this->errors as $key ) {
					$html .= $this->get_message_html( $key );
				}

			} else {
				$html = $this->get_message_html( $this->get_action() . 'd' );
			}
		}

		/**
		 * Filter the form response HTML
		 *
		 * Use this to add your own HTML to the form response. The form instance is passed to the callback function.
		 *
		 * @since 3.0
		 *
		 * @param string $html The complete HTML string of the response, excluding the wrapper element.
		 * @param MC4WP_Form $form The form object
		 */
		$html = (string) apply_filters( 'mc4wp_form_response_html', $html, $form );

		// wrap entire response in div, regardless of a form was submitted
		$html = '<div class="mc4wp-response">' . $html . '</div>';
		return $html;
	}

	/**
	 * Get HTML string for this form.
	 *
	 * If you want to output a form, use `mc4wp_show_form` instead as it.
	 *
	 * @param string $element_id
	 * @param array $config
	 *
	 * @return string
	 */
	public function get_html( $element_id = 'mc4wp-form', array $config = array() ) {
		$element = new MC4WP_Form_Element( $this, $element_id, $config );
		$html = $element->generate_html();
		return $html;
	}

	/**
	 * Maps registered messages to a message type
	 *
	 * @todo Move to Message object?
	 * @return array
	 */
	public function get_message_types() {
		$form = $this;
		$message_types = array(
			'subscribed' => 'success',
			'unsubscribed' => 'success',
			'error' => 'error',
			'invalid_email' => 'error',
			'required_field_missing' => 'error',
			'already_subscribed' => 'notice',
			'not_subscribed' => 'notice',
			'no_lists_selected' => 'error',
		);

		/**
		 * Filters the type for each error / success message.
		 *
		 * @see `mc4wp_form_errors`
		 * @see `mc4wp_form_messages`
		 * @param array $message_types
		 * @param MC4WP_Form $form
		 */
		$message_types = (array) apply_filters( 'mc4wp_form_message_types', $message_types, $form );

		return $message_types;
	}

	/**
	 * @return array
	 */
	protected function load_settings() {
		$defaults = include MC4WP_PLUGIN_DIR . 'config/default-form-settings.php';
		$settings = $defaults;
		$form = $this;

		// get stored settings from post meta
		$meta = get_post_meta( $this->ID, '_mc4wp_settings', true );

		if( is_array( $meta ) ) {
			// merge with defaults
			$settings = array_merge( $settings, $meta );
		}

		/**
		 * Filters the form settings
		 *
		 * @param array $settings
		 * @param MC4WP_Form $form
		 */
		$settings = (array) apply_filters( 'mc4wp_form_settings', $settings, $form );

		return $settings;
	}

	/**
	 * @return array
	 */
	protected function load_messages() {
		$defaults = include MC4WP_PLUGIN_DIR . 'config/default-form-messages.php';
		$messages = array();
		$form = $this;

		foreach( $defaults as $key => $message ) {
			$message = get_post_meta( $this->ID, $key, true );
			$messages[ $key ] = ( ! empty( $message ) ) ? $message : $defaults[ $key ];
		}

		/**
		 * Filters the form messages
		 *
		 * @param array $messages
		 * @param MC4WP_Form
		 */
		$messages = (array) apply_filters( 'mc4wp_form_messages', $messages, $form );

		return $messages;
	}

	/**
	 * Does this form has a field of the given type?
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public function has_field_type( $type ) {
		return in_array( strtolower( $type ), $this->get_field_types() );
	}

	/**
	 * Get an array of field types which are present in this form.
	 *
	 * @return array
	 */
	public function get_field_types() {
		preg_match_all( '/type=\"(\w+)?\"/', strtolower( $this->content ), $result );
		$field_types = $result[1];

		return $field_types;
	}

	/**
	 * Get HTML string for a message, including wrapper element.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_message_html( $key ) {
		$message = $this->get_message( $key );
		$type = $this->get_message_type( $key );

		$html = sprintf( '<div class="mc4wp-alert mc4wp-%s"><p>%s</p></div>', esc_attr( $type ), $message );
		$html = (string) apply_filters( 'mc4wp_form_message_html', $html, $this );

		return $html;
	}

	/**
	 * Get raw message string
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_message( $key ) {

		if( isset( $this->messages[ $key ] ) ) {
			return $this->messages[ $key ];
		}

		// default to error message
		return $this->messages['error'];
	}

	/**
	 * Get message type for a message.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_message_type( $key ) {

		$message_types = $this->get_message_types();

		if( isset( $message_types[ $key ] ) ) {
			return $message_types[ $key ];
		}

		return 'error';
	}

	/**
	 * Output this form
	 *
	 * @return string
	 */
	public function __toString() {
		return mc4wp_show_form( $this->ID, array(), false );
	}

	/**
	 * Get "redirect to url after success" setting for this form
	 *
	 * @return string
	 */
	public function get_redirect_url() {
		$form = $this;
		$url = trim( $this->settings['redirect'] );

		/**
		 * Filters the redirect URL setting
		 *
		 * @param string $url
		 * @param MC4WP_Form $form
		 */
		$url = (string) apply_filters( 'mc4wp_form_redirect_url', $url, $form );
		return $url;
	}

	/**
	 * Is this form valid?
	 *
	 * Will always return true if the form is not yet submitted. Otherwise, it will run validation and store any errors.
	 * This method should be called before `get_errors()`
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function is_valid() {

		if( ! $this->is_submitted ) {
			return true;
		}

		$form = $this;

		// validate config
		$validator = new MC4WP_Validator( $this->config );
		$validator->add_rule( 'lists', 'not_empty', 'no_lists_selected' );
		$valid = $validator->validate();

		// validate internal fields
		if( $valid ) {
			$validator = new MC4WP_Validator( $this->raw_data );
			$validator->add_rule( '_mc4wp_timestamp', 'range', 'spam', array( 'max' => time() - 2 ) );
			$validator->add_rule( '_mc4wp_honeypot', 'empty', 'spam' );
			$validator->add_rule( '_mc4wp_form_nonce', 'valid_nonce', 'spam', array( 'action' => '_mc4wp_form_nonce' ) );
			$valid = $validator->validate();

			// validate actual (visible) fields
			if( $valid ) {
				$validator = new MC4WP_Validator( $this->data );
				$validator->add_rule( 'EMAIL', 'email', 'invalid_email' );
				foreach( $this->get_required_fields() as $field ) {
					$validator->add_rule( $field, 'not_empty', 'required_field_missing' );
				}
				$valid = $validator->validate();
			}
		}

		// get validation errors
		$errors = $validator->get_errors();

		/**
		 * Filters whether this form has errors. Runs only when a form is submitted.
		 * Expects an array of message keys.
		 *
		 * @param array $errors
		 * @param MC4WP_Form $form
		 */
		$this->errors = (array) apply_filters( 'mc4wp_form_errors', $errors, $form );

		return $valid;
	}

	/**
	 * Handle an incoming request. Should be called before calling `is_valid`.
	 *
	 * @param MC4WP_Request $request
	 * @return void
	 */
	public function handle_request( MC4WP_Request $request ) {
		$this->is_submitted = true;
		$this->raw_data = $request->post->all();
		$this->data = $this->parse_request_data( $request );

		// update config from data
		if( isset( $this->raw_data['_mc4wp_lists'] ) ) {
			$this->set_config( array( 'lists' => $this->raw_data['_mc4wp_lists'] ) );
		}
	}

	/**
	 * Parse a request for data which should be binded to `$data` property.
	 *
	 * This does the following on all post data.
	 *
	 * - Removes fields starting with an underscore.
	 * - Remove fields which are set to be ignored.
	 * - Uppercase all field names
	 *
	 * @param MC4WP_Request $request
	 *
	 * @return array
	 */
	protected function parse_request_data( MC4WP_Request $request ) {
		$form = $this;

		// get all fields that do NOT start with an underscore.
		$data = $request->post->all_without_prefix( '_' );

		// uppercase all field keys
		$data = array_change_key_case( $data, CASE_UPPER );

		// get rid of ignored field names
		$ignored_field_names = array();

		/**
		 * Filters field names which should be ignored when showing data.
		 *
		 * @param array $ignored_field_names Array of ignored field names
		 * @param MC4WP_Form $form The form instance.
		 */
		$ignored_field_names = apply_filters( 'mc4wp_form_ignored_field_names', $ignored_field_names, $form );
		$data = array_diff_key( $data, array_flip( $ignored_field_names ) );

		return $data;
	}

	/**
	 * Update configuration for this form
	 *
	 * @param array $config
	 * @return array
	 */
	public function set_config( array $config ) {
		$this->config = array_merge( $this->config, $config );

		// make sure lists is an array
		if( ! is_array( $this->config['lists'] ) ) {
			$this->config['lists'] = array_map( 'trim', explode(',', $this->config['lists'] ) );
		}

		// make sure action is valid
		if( ! in_array( $this->config['action'], array( 'subscribe', 'unsubscribe' ) ) ) {
			$this->config['action'] = 'subscribe';
		}



		return $this->config;
	}

	/**
	 * Get email type for subscribers using this form
	 *
	 * @return string
	 */
	public function get_email_type() {
		$form = $this;
		$email_type = $this->config['email_type'];

		/**
		 * Filters email type for new subscribers.
		 *
		 * @param string $email_type
		 */
		$email_type = (string) apply_filters( 'mc4wp_email_type', $email_type );

		/**
		 * Filters email type for people that subscribe using this form.
		 *
		 * @param string $email_type
		 * @param MC4WP_Form $form
		 */
		$email_type = (string) apply_filters( 'mc4wp_form_email_type', $email_type, $form );
		return $email_type;
	}

	/**
	 * Get MailChimp lists this form subscribes to
	 *
	 * @return array
	 */
	public function get_lists() {

		$lists = $this->config['lists'];
		$form = $this;

		/**
		 * Filters MailChimp lists new subscribers should be added to.
		 *
		 * @param array $lists
		 */
		$lists = (array) apply_filters( 'mc4wp_lists', $lists );

		/**
		 * Filters MailChimp lists new subscribers coming from this form should be added to.
		 *
		 * @param array $lists
		 * @param MC4WP_Form $form
		 */
		$lists = (array) apply_filters( 'mc4wp_form_lists', $lists, $form );
		return $lists;
	}

	/**
	 * Does this form have errors?
	 *
	 * Should always evaluate to false when form has not been submitted.
	 *
	 * @see `mc4wp_form_errors` filter.
	 * @return bool
	 */
	public function has_errors() {
		return count( $this->errors ) > 0;
	}

	/**
	 * Add an error to this form
	 *
	 * @todo find a way to pass fully qualified message here (message + type)
	 * @param string $error_code
	 */
	public function add_error( $error_code ) {

		if( ! in_array( $error_code, $this->errors ) ) {
			$this->errors[] = $error_code;
		}
	}

	/**
	 * Get the form action
	 *
	 * Valid return values are "subscribe" and "unsubscribe"
	 *
	 * @return string
	 */
	public function get_action() {
		return $this->config['action'];
	}

	/**
	 * Get array of name attributes for the required fields in this form.
	 *
	 * @return array
	 */
	public function get_required_fields() {
		return explode( ',', $this->settings['required_fields'] );
	}
}