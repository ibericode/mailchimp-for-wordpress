<?php

/**
 * Class MC4WP_Form
 *
 * @author Danny van Kooten
 * @package MailChimp for WordPress
 * @api
 * @since 3.0
 */
class MC4WP_Form {

	/**
	 * @var int
	 */
	public $ID = 0;

	/**
	 * @var string
	 */
	public $name = 'Default Form';

	/**
	 * @var string
	 */
	public $content = '';

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var array
	 */
	public $messages = array();

	/**
	 * @var MC4WP_Form_Request
	 */
	public $request;

	/**
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * @var array
	 */
	public static $instances = array();

	/**
	 * @var array Array of error code's
	 * @todo Change to actual error messages?
	 */
	public $errors = array();


	/**
	 * @param int $form_id
	 * @return MC4WP_Form
	 * @throws Exception
	 */
	public static function get_instance( $form_id ) {

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
	 * @param int $id
	 * @throws Exception
	 */
	public function __construct( $id = 0 ) {
		$this->post = $post = get_post( (int) $id );

		if( ! is_object( $post ) || ! isset( $post->post_type ) || $post->post_type !== 'mc4wp-form' ) {
			$message = sprintf( __( 'There is no form with ID %d, perhaps it was deleted?', 'mailchimp-for-wp' ), $id );
			throw new Exception( $message );
		}

		$this->ID = $id;
		$this->name = $post->post_title;
		$this->content = $post->post_content;
		$this->settings = $this->load_settings();
		$this->messages = $this->load_messages();
	}



	/**
	 * Is this form submitted?
	 * @return bool
	 */
	public function is_submitted() {
		$form_submitted = $this->request instanceof MC4WP_Form_Request;
		return $form_submitted;
	}

	/**
	 * @return string
	 */
	public function get_response_html() {

		if( ! $this->is_submitted() ) {
			return '';
		}

		$html = '';

		if( ! empty( $this->errors ) ) {

			// create html string of all errors
			foreach( $this->errors as $key ) {
				$html .= $this->get_message_html( $key );
			}
		} else {
			$result_code = $this->request->result_code;
			$html = $this->get_message_html( $result_code );
		}

		return (string) apply_filters( 'mc4wp_form_response_html', $html, $this );
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
		static $field_types;

		if( ! $field_types ) {
			preg_match_all( '/type=\"(\w+)?\"/', strtolower( $this->content ), $result );
			$field_types = $result[1];
		}

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
	 * @return string
	 */
	public function __toString() {
		return mc4wp_show_form( $this->ID, array(), false );
	}

	/**
	 * Get "redirect to url after success" setting for this form
	 */
	public function get_redirect_url() {
		$url = trim( $this->settings['redirect'] );
		$url = (string) apply_filters( 'mc4wp_form_redirect_url', $url, $this );
		return $url;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function is_valid() {

		if( ! $this->is_submitted() ) {
			return true;
		}

		$fields = $this->request->user_data;
		$fields += array(
			'_TIMESTAMP' => $this->request->internal_data['timestamp'],
			'_HONEYPOT' => $this->request->internal_data['honeypot'],
			'_NONCE' => $this->request->internal_data['form_nonce'],
			'_LISTS' => $this->request->get_lists()
		);

		$validator = new MC4WP_Validator( $fields );
		$validator = $this->set_validation_rules( $validator );
		$valid = $validator->validate();

		// store validation errors
		$this->errors = $validator->get_errors();

		/**
		 * @since 3.0
		 */
		$this->errors = (array) apply_filters( 'mc4wp_form_errors', $this->errors );

		return $valid;
	}

	/**
	 * @param MC4WP_Validator $validator
	 * @return MC4WP_Validator
	 */
	public function set_validation_rules( MC4WP_Validator $validator ) {

		// set fields which can't be empty
		$validator->add_rule( 'EMAIL', 'not_empty', 'invalid_email' );
		$validator->add_rule( 'EMAIL', 'email', 'invalid_email' );

		// @todo Get this from fields with `required` attribute (+ MailChimp required fields)
		//$validator->add_rule( 'FNAME', 'not_empty', 'required_field_missing' );

		// set minimum for timestamp
		$validator->add_rule( '_TIMESTAMP', 'range', 'spam', array( 'max' => time() - 2 ) );

		// honeypot must be empty
		$validator->add_rule( '_HONEYPOT', 'empty', 'spam' );

		// nonce field must be valid nonce
		$validator->add_rule( '_NONCE', 'valid_nonce', 'spam', array( 'action' => '_mc4wp_form_nonce' ) );
		$validator->add_rule( '_LISTS', 'not_empty', 'no_lists_selected' );

		return $validator;
	}
}