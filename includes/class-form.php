<?php

class MC4WP_Form {

	/**
	 * @var MC4WP_Form
	 */
	private static $instance;

	/**
	 * @param iMC4WP_Request $request
	 * @return MC4WP_Form|null
	 */
	public static function get( iMC4WP_Request $request = null ) {

		// has instance been created already?
		if( self::$instance ) {
			$form = self::$instance;
		} else {
			// create a new instance
			$form = new MC4WP_Form( $request );
			self::$instance = $form;
		}

		// attach request to form
		if( $request && ! $form->has_request( $request ) ) {
			$form->attach_request( $request );
		}

		return $form;

	}

	/**
	 * @var int
	 */
	public $ID = 0;

	/**
	 * @var string
	 */
	public $content = '';

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var iMC4WP_Request
	 */
	public $request;

	/**
	 * @param iMC4WP_Request $request
	 */
	private function __construct( iMC4WP_Request $request = null ) {
		$this->ID = 0;
		$this->name = 'Default Form';
		$this->settings = $this->load_settings();
		$this->content = $this->settings['markup'];
		$this->request = $request;
	}

	/**
	 * Simple check to see if form contains a given field type
	 *
	 * @param $field_type
	 *
	 * @return bool
	 */
	public function contains_field_type( $field_type ) {
		$html = sprintf( ' type="%s" ', $field_type );
		return stristr( $this->content, $html ) !== false;
	}


	/**
	 * @param string $element_id
	 * @param array $attributes
	 * @param string $response_html
	 * @return string
	 */
	public function get_visible_fields( $element_id, array $attributes = array(), $response_html = '' ) {

		$replacements = array(
			'{n}' => $element_id,
			'{response}' => $response_html,
		);

		$visible_fields = MC4WP_Tools::replace_variables( $this->content, $replacements, array_values( $this->settings['lists'] ) );

		// insert captcha
		if( function_exists( 'cptch_display_captcha_custom' ) ) {
			$captcha_fields = '<input type="hidden" name="_mc4wp_has_captcha" value="1" /><input type="hidden" name="cntctfrm_contact_action" value="true" />' . cptch_display_captcha_custom();
			$visible_fields = str_ireplace( array( '{captcha}', '[captcha]' ), $captcha_fields, $visible_fields );
		}

		/**
		 * @filter mc4wp_form_content
		 * @param int $form_id The ID of the form that is being shown
		 * @expects string
		 *
		 * Can be used to customize the content of the form mark-up, eg adding additional fields.
		 */
		$visible_fields = (string) apply_filters( 'mc4wp_form_content', $visible_fields, $this->ID );

		return $visible_fields;
	}

	/**
	 * @param string $element_id
	 * @param array $attributes Attributes passed to the shortcode
	 * @return string
	 */
	public function get_hidden_fields( $element_id, $attributes = array() ) {

		// hidden fields
		$hidden_fields = '<div style="position: absolute; ' . ( is_rtl() ? 'right' : 'left' ) . ': -5000px;"><input type="text" name="_mc4wp_h_'. md5( time() ).'" value="" tabindex="-1" autocomplete="off" /></div>';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_timestamp" value="'. time() . '" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_id" value="'. $this->ID .'" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_element_id" value="'. esc_attr( $element_id ) .'" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_submit" value="1" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';


		// was "lists" parameter passed in shortcode arguments?
		if( isset( $attributes['lists'] ) && ! empty( $attributes['lists'] ) ) {
			$lists_string = ( is_array( $attributes['lists'] ) ) ? join( ',', $attributes['lists'] ) : $attributes['lists'];
			$hidden_fields .= '<input type="hidden" name="_mc4wp_lists" value="'. $lists_string . '" />';
		}

		return (string) $hidden_fields;
	}

	/**
	 * Is this form submitted?
	 * @param string $element_id
	 * @return bool
	 */
	public function is_submitted( $element_id = null ) {

		// is this form (any instance) submitted)
		$form_submitted = $this->request instanceof iMC4WP_Request;

		// if an element ID is given, only return true if that specific element is submitted
		if( $element_id ) {
			return ( $form_submitted && $this->request->form_element_id == $element_id );
		}

		return $form_submitted;
	}

	/**
	 * @return string
	 */
	protected function get_response_position() {

		/**
		 * @deprecated
		 * @use `mc4wp_form_response_position` instead
		 */
		$message_position = (string) apply_filters( 'mc4wp_form_message_position', 'after' );

		/**
		 * @filter mc4wp_form_message_position
		 * @expects string before|after
		 *
		 * Can be used to change the position of the form success & error messages.
		 * Valid options are 'before' or 'after'
		 */
		$response_position = (string) apply_filters( 'mc4wp_form_response_position', $message_position );

		// check if content contains {response} tag
		if( stripos( $this->content, '{response}' ) !== false ) {
			$response_position = '';
		}

		return $response_position;
	}

	/**
	 * @param string $response_html
	 * @return string
	 */
	protected function get_html_before_fields( $response_html = '' ) {
		$before_fields = (string) apply_filters( 'mc4wp_form_before_fields', '' );

		if( $this->get_response_position() === 'before' ) {
			$before_fields = $response_html . $response_html;
		}

		return $before_fields;
	}

	/**
	 * @param string $response_html
	 * @return string
	 */
	protected function get_html_after_fields( $response_html = '' ) {
		$after_fields = (string) apply_filters( 'mc4wp_form_after_fields', '' );

		if( $this->get_response_position() === 'after' ) {
			$after_fields = $response_html . $after_fields;
		}

		return $after_fields;
	}

	/**
	 * Get the `action` attribute of the form element.
	 *
	 * @return string
	 */
	protected function get_form_action_attribute() {

		/**
		 * @filter `mc4wp_form_action`
		 * @expects string
		 * @param MC4WP_Form $form
		 */
		$form_action = apply_filters( 'mc4wp_form_action', null, $this );

		if( is_string( $form_action ) ) {
			$form_action_attribute = sprintf( 'action="%s"', esc_attr( $form_action ) );
		} else {
			$form_action_attribute = '';
		}

		return $form_action_attribute;
	}

	/**
	 * @param string $element_id
	 * @param array $attributes
	 * @return string
	 */
	public function generate_html( $element_id = 'mc4wp-form', array $attributes = array() ) {

		// generate response html
		$response_html = ( $this->is_submitted( $element_id ) ) ? $this->request->get_response_html() : '';

		// Some vars we might fill later on
		$form_opening_html = '';
		$form_closing_html = '';
		$visible_fields = '';
		$hidden_fields = '';

		// Start building content string
		$opening_html = '<!-- MailChimp for WordPress v' . MC4WP_LITE_VERSION . ' - https://wordpress.org/plugins/mailchimp-for-wp/ -->';
		$opening_html .= '<div id="' . esc_attr( $element_id ) . '" class="' . esc_attr( $this->get_css_classes( $element_id ) ) . '">';
		$before_fields = apply_filters( 'mc4wp_form_before_fields', '' );
		$after_fields = apply_filters( 'mc4wp_form_after_fields', '' );
		$before_form = $this->get_html_before_fields( $response_html );
		$after_form = $this->get_html_after_fields( $response_html );
		$closing_html = '</div><!-- / MailChimp for WordPress Plugin -->';

		// only generate form & fields HTML if necessary
		if( ! $this->is_submitted( $element_id )
		    || ! $this->settings['hide_after_success']
		    || ! $this->request->success ) {

			$form_opening_html = '<form method="post" '. $this->get_form_action_attribute() .'>';
			$visible_fields = $this->get_visible_fields( $element_id, $attributes, $response_html );
			$hidden_fields = $this->get_hidden_fields( $element_id, $attributes );
			$form_closing_html = '</form>';
		}

		ob_start();

		// echo HTML parts of form
		echo $opening_html;
		echo $before_form;
		echo $form_opening_html;
		echo $before_fields;
		echo $visible_fields;
		echo $hidden_fields;
		echo $after_fields;
		echo $form_closing_html;
		echo $after_form;
		echo $closing_html;

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * @param string $element_id
	 * @param array $attributes
	 * @param bool  $echo
	 *
	 * @return string
	 */
	public function output( $element_id = 'mc4wp-form', array $attributes = array(), $echo = true ) {

		$html = $this->generate_html( $element_id, $attributes );

		if( $echo ) {
			echo $html;
		}

		return $html;
	}


	/**
	 * Get a space separated list of CSS classes for this form
	 *
	 * @param string $element_id
	 * @return string
	 */
	public function get_css_classes( $element_id ) {

		/**
		 * @filter mc4wp_form_css_classes
		 * @expects array
		 *
		 * Can be used to add additional CSS classes to the form container
		 */
		$css_classes = apply_filters( 'mc4wp_form_css_classes', array( 'form' ), $this );

		// the following classes MUST be used
		$css_classes[] = 'mc4wp-form';

		// Add form classes if this specific form instance was submitted
		if( $this->is_submitted( $element_id ) ) {

			$css_classes[] = 'mc4wp-form-submitted';

			if( $this->request->success ) {
				$css_classes[] = 'mc4wp-form-success';
			} else {
				$css_classes[] = 'mc4wp-form-error';
			}

		}

		return implode( ' ', $css_classes );
	}

	/**
	 * @return array
	 */
	public function load_settings() {
		return mc4wp_get_options( 'form' );
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
	public function get_messages() {

		$messages = array(

			// email was successfully subscribed to the selected list(s)
			'subscribed' => array(
				'type' => 'success',
				'text' => $this->settings['text_subscribed'],
			),

			// email was successfully unsubscribed from the selected list(s)
			'unsubscribed' => array(
				'type' => 'success',
				'text' => $this->settings['text_unsubscribed'],
			),

			// a general (unknown) error occurred
			'error' => array(
				'type' => 'error',
				'text' => $this->settings['text_error'],
			),

			// an invalid email was given
			'invalid_email' => array(
				'type' => 'error',
				'text' => $this->settings['text_invalid_email'],
			),

			// the captcha was not filled correctly
			'invalid_captcha' => array(
				'type' => 'error',
				'text' => $this->settings['text_invalid_captcha'],
			),

			// a required field is missing for the selected list(s)
			'required_field_missing' => array(
				'type' => 'error',
				'text' => $this->settings['text_required_field_missing'],
			),

			// email is already subscribed to the selected list(s)
			'already_subscribed' => array(
				'type' => 'notice',
				'text' => $this->settings['text_already_subscribed'],
			),

			// email is not subscribed on the selected list(s)
			'not_subscribed' => array(
				'type' => 'notice',
				'text' => $this->settings['text_not_subscribed'],
			),
		);

		// add some admin-only messages
		if( current_user_can( 'manage_options' ) ) {
			$messages['no_lists_selected'] = array(
				'type' => 'notice',
				'text' => sprintf( __( 'You did not select a list in <a href="%s">your form settings</a>.', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp-form-settings' ) )
			);
		}

		/**
		 * @filter mc4wp_form_messages
		 * @expects array
		 *
		 * Allows registering custom form messages, useful if you're using custom validation using the `mc4wp_valid_form_request` filter.
		 */
		$messages = apply_filters( 'mc4wp_form_messages', $messages, $this->ID );

		return (array) $messages;
	}

	/**
	 * @param iMC4WP_Request $request
	 *
	 * @return bool
	 */
	protected function has_request( iMC4WP_Request $request ) {
		return $this->request === $request;
	}

	/**
	 * @param iMC4WP_Request $request
	 */
	protected function attach_request( iMC4WP_Request $request ) {
		$this->request = $request;
	}

}