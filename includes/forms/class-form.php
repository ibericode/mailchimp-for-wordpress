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
 * @api This class is intended for public use.
 * @since 3.0
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

		$form_id = (int) $form_id;

		if( empty( $form_id ) ) {
			$form_id = (int) get_option( 'mc4wp_default_form_id', 0 );
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
	 * @var array Array of the data that was submitted, in field => value pairs.
	 */
	public $data = array();

	/**
	 * @var array
	 */
	public $config = array(
		'action' => 'subscribe',
		'lists' => array(),
		'email_type' => 'html'
	);

	/**
	 * @param int $id
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
	 *
	 * @return string
	 */
	public function get_response_html() {

		$html = '';

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
		 * @since
		 *
		 * @param string $html The complete HTML string of the response, excluding the wrapper element.
		 * @param MC4WP_Form $this  The form object
		 */
		$html = (string) apply_filters( 'mc4wp_form_response_html', $html, $this );

		// wrap entire response in div, regardless of a form was submitted
		$html = '<div class="mc4wp-response">' . $html . '</div>';
		return $html;
	}

	/**
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
	 * Maps registered messages to a type
	 *
	 * @return array
	 */
	public function get_message_types() {

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
		 * @filter mc4wp_form_message_types
		 * @expects array
		 *
		 * Allows registering custom form messages, useful if you're using custom validation using the `mc4wp_valid_form_request` filter.
		 */
		$message_types = (array) apply_filters( 'mc4wp_form_message_types', $message_types, $this );

		return $message_types;
	}

	/**
	 * @return array
	 */
	protected function load_settings() {
		$defaults = include MC4WP_PLUGIN_DIR . 'config/default-form-settings.php';
		$settings = $defaults;

		// get stored settings from post meta
		$meta = get_post_meta( $this->ID, '_mc4wp_settings', true );

		if( is_array( $meta ) ) {
			// merge with defaults
			$settings = array_merge( $settings, $meta );
		}

		$settings = (array) apply_filters( 'mc4wp_form_settings', $settings, $this );

		return $settings;
	}

	/**
	 * @return array
	 */
	protected function load_messages() {
		$defaults = include MC4WP_PLUGIN_DIR . 'config/default-form-messages.php';
		$messages = array();

		foreach( $defaults as $key => $message ) {
			$message = get_post_meta( $this->ID, $key, true );
			$messages[ $key ] = ( ! empty( $message ) ) ? $message : $defaults[ $key ];
		}

		$messages = (array) apply_filters( 'mc4wp_form_messages', $messages, $this );

		return $messages;
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	public function has_field_type( $type ) {
		return in_array( strtolower( $type ), $this->get_field_types() );
	}

	/**
	 * @return array
	 */
	public function get_field_types() {
		preg_match_all( '/type=\"(\w+)?\"/', strtolower( $this->content ), $result );
		$field_types = $result[1];

		return $field_types;
	}

	/**
	 * @param $key
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
	 * @param $key
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
	 * @param $key
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
		$url = trim( $this->settings['redirect'] );
		$url = (string) apply_filters( 'mc4wp_form_redirect_url', $url, $this );
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

		$validator = new MC4WP_Validator();

		// validate config
		$validator->set_fields( $this->config );
		$validator->add_rule( 'lists', 'not_empty', 'no_lists_selected' );
		$validator->add_rule( 'timestamp', 'range', 'spam', array( 'max' => time() - 2 ) );
		$validator->add_rule( 'honeypot', 'empty', 'spam' );
		$validator->add_rule( 'form_nonce', 'valid_nonce', 'spam', array( 'action' => '_mc4wp_form_nonce' ) );
		$valid = $validator->validate();

		if( $valid ) {
			// validate fields
			$validator->set_fields( $this->data );

			foreach( $this->get_required_fields() as $field ) {
				$validator->add_rule( $field, 'not_empty', 'required_field_missing' );
			}

			$validator->add_rule( 'EMAIL', 'email', 'invalid_email' );

			//$validator->add_rule( 'FNAME', 'not_empty', 'required_field_missing' );
			$valid = $validator->validate();
		}

		// get validation errors
		$errors = $validator->get_errors();

		/**
		 * @since 3.0
		 */
		$this->errors = (array) apply_filters( 'mc4wp_form_errors', $errors, $this );

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
		$this->data = $request->get_all_params_without_prefix( '_', CASE_UPPER );
		$config = $request->get_all_params_with_prefix( '_mc4wp_', CASE_LOWER );
		$this->set_config( $config );
	}

	/**
	 * Update configuration for this form
	 *
	 * @param array $config
	 * @return array
	 */
	public function set_config( array $config ) {

		// @todo decide if we want the nonce etc. in this array
		// @todo sanitize values (like subscribe)

		if( isset( $config['lists'] ) ) {
			if( ! is_array( $config['lists'] ) ) {
				$config['lists'] = array_map( 'trim', explode( ',', $config['lists'] ) );
			}
		}

		$this->config = array_merge( $this->config, $config );
		return $this->config;
	}

	/**
	 * Get email type for subscribers using this form
	 *
	 * @return string
	 */
	public function get_email_type() {
		$email_type = (string) apply_filters( 'mc4wp_email_type', $this->config['email_type'] );
		$email_type = (string) apply_filters( 'mc4wp_form_email_type', $email_type, $this );
		return $email_type;
	}

	/**
	 * Get MailChimp lists this form subscribes to
	 *
	 * @return array
	 */
	public function get_lists() {
		$lists = (array) apply_filters( 'mc4wp_lists', $this->config['lists'] );
		$lists = (array) apply_filters( 'mc4wp_form_lists', $lists, $this );
		return $lists;
	}

	/**
	 * Does this form have errors?
	 *
	 * Should always evaluate to false when form has not been submitted.
	 *
	 * @return bool
	 */
	public function has_errors() {
		return count( $this->errors ) > 0;
	}

	/**
	 * Add an error for this form
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
	 * Get required fields for this form
	 *
	 * @return array
	 */
	public function get_required_fields() {
		return explode( ',', $this->settings['required_fields'] );
	}

}