<?php

/**
 * Class MC4WP_Form_Element
 *
 * @since 3.0
 * @internal
 */
class MC4WP_Form_Element {

	/**
	 * @var string
	 */
	public $ID;

	/**
	 * @var MC4WP_Form
	 */
	public $form;

	/**
	 * @var array
	 *
	 * Can be used to set element-specific config settings. Accepts the following keys.
	 *
	 * - lists: Customized number of MailChimp list ID's to subscribe to.
	 * - email_type: The email type
	 */
	public $config = array();

	/**
	 * @var bool
	 */
	public $is_submitted = false;

	/**
	 * @param MC4WP_Form $form
	 * @param string $ID
	 * @param $config array
	 */
	public function __construct( MC4WP_Form $form, $ID, $config = array() ) {
		$this->form = $form;
		$this->ID = $ID;
		$this->config = $config;
		$this->is_submitted = $this->form->is_submitted
		                      && ! empty( $this->form->config['form_element_id'] )
		                      && $this->form->config['form_element_id'] == $this->ID;
	}


	/**
	 * @return string
	 */
	public function get_visible_fields() {

		/**
		 * @filter mc4wp_form_content
		 * @param int $form_id The ID of the form that is being shown
		 * @expects string
		 *
		 * Can be used to customize the content of the form mark-up, eg adding additional fields.
		 */
		$visible_fields = (string) apply_filters( 'mc4wp_form_content', $this->form->content, $this->form );

		return $visible_fields;
	}

	/**
	 * @return string
	 */
	public function get_hidden_fields() {

		// hidden fields
		$hidden_fields = '<div style="display: none;"><input type="text" name="_mc4wp_honeypot" value="" tabindex="-1" autocomplete="off" /></div>';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_timestamp" value="'. time() . '" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_id" value="'. esc_attr( $this->form->ID ) .'" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_element_id" value="'. esc_attr( $this->ID ) .'" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';

		// was "lists" parameter passed in shortcode arguments?
		if( ! empty( $this->config['lists'] ) ) {
			$lists_string = is_array( $this->config['lists'] ) ? join( ',', $this->config['lists'] ) : $this->config['lists'];
			$hidden_fields .= '<input type="hidden" name="_mc4wp_lists" value="'. esc_attr( $lists_string ) . '" />';
		}

		return (string) $hidden_fields;
	}

	/**
	 * @return string
	 */
	protected function get_response_position() {

		/**
		 * @filter mc4wp_form_message_position
		 * @expects string before|after
		 *
		 * Can be used to change the position of the form success & error messages.
		 * Valid options are 'before' or 'after'
		 */
		$response_position = (string) apply_filters( 'mc4wp_form_response_position', 'after' );

		// check if content contains {response} tag
		if( stripos( $this->form->content, '{response}' ) !== false ) {
			$response_position = '';
		}

		return $response_position;
	}

	/**
	 * @param string $response_html
	 * @return string
	 */
	protected function get_html_before_form( $response_html = '' ) {
		$html = (string) apply_filters( 'mc4wp_form_before_form', '', $this );

		if( $this->get_response_position() === 'before' ) {
			$html = $html . $response_html;
		}

		return $html;
	}

	/**
	 * @param string $response_html
	 * @return string
	 */
	protected function get_html_after_form( $response_html = '' ) {
		$html = (string) apply_filters( 'mc4wp_form_after_form', '', $this );

		if( $this->get_response_position() === 'after' ) {
			$html = $response_html . $html;
		}

		return $html;
	}

	/**
	 * Get all HTMl attributes for the form element
	 *
	 * @return string
	 */
	protected function get_form_element_attributes() {

		$attributes = array();

		/**
		 * @filter `mc4wp_form_action`
		 * @expects string
		 * @param MC4WP_Form $form
		 */
		$form_action = apply_filters( 'mc4wp_form_action', null, $this->form );

		if( is_string( $form_action ) ) {
			$attributes['action'] = $form_action;
		}

		/**
		 * @filter `mc4wp_form_attributes`
		 */
		$attributes = (array) apply_filters( 'mc4wp_form_element_attributes', $attributes, $this->form );

		// build string of key="value" from array
		$string = '';
		foreach( $attributes as $name => $value ) {
			$string .= sprintf( '%s="%s"', $name, esc_attr( $value ) );
		}

		return $string;
	}

	/**
	 * @param array|null $config Use this to override the configuration for this form element
	 * @return string
	 */
	public function generate_html( array $config = null ) {

		if( $config ) {
			$this->config = $config;
		}

		// generate response html
		$response_html = $this->form->get_response_html();

		// Some vars we might fill later on
		$form_opening_html = '';
		$form_closing_html = '';
		$visible_fields = '';
		$hidden_fields = '';

		// Start building content string
		$opening_html = '<!-- MailChimp for WordPress v' . MC4WP_VERSION . ' - https://wordpress.org/plugins/mailchimp-for-wp/ -->';
		$opening_html .= '<div id="' . esc_attr( $this->ID ) . '" class="' . esc_attr( $this->get_css_classes() ) . '">';
		$before_fields = apply_filters( 'mc4wp_form_before_fields', '', $this );
		$after_fields = apply_filters( 'mc4wp_form_after_fields', '', $this );
		$before_form = $this->get_html_before_form( $response_html );
		$after_form = $this->get_html_after_form( $response_html );
		$closing_html = '</div><!-- / MailChimp for WordPress Plugin -->';

		// only generate form & fields HTML if necessary
		if( ! $this->is_submitted
		    || ! $this->form->settings['hide_after_success']
			|| $this->form->has_errors()) {

			$form_opening_html = '<form method="post" '. $this->get_form_element_attributes() .'>';
			$visible_fields = $this->get_visible_fields();
			$hidden_fields = $this->get_hidden_fields();
			$form_closing_html = '</form>';
		}

		// concatenate everything
		$output = $opening_html .
		          $before_form .
		          $form_opening_html .
		          $before_fields .
		          $visible_fields .
		          $hidden_fields .
		          $after_fields .
		          $form_closing_html .
		          $after_form .
		          $closing_html;

		return $output;
	}

	/**
	 * Get a space separated list of CSS classes for this form
	 *
	 * @return string
	 */
	public function get_css_classes() {

		/**
		 * @filter mc4wp_form_css_classes
		 * @expects array
		 *
		 * Can be used to add additional CSS classes to the form container
		 */
		$css_classes = apply_filters( 'mc4wp_form_css_classes', array( 'form' ), $this->form );

		// the following classes MUST be used
		$css_classes[] = 'mc4wp-form';
		$css_classes[] = 'mc4wp-form-' . $this->form->ID;

		// Add form classes if this specific form instance was submitted
		if( $this->is_submitted ) {
			$css_classes[] = 'mc4wp-form-submitted';
			$css_classes[] = ( empty( $this->errors ) ) ?  'mc4wp-form-success' : 'mc4wp-form-error';
		}

		// add class for CSS targetting
		if( $this->form->settings['css'] ) {

			if( strpos( $this->form->settings['css'], 'form-theme' ) === 0 ) {
				$css_classes[] = 'mc4wp-form-theme';
			}

			$css_classes[] = 'mc4wp-' . $this->form->settings['css'];
		}

		return implode( ' ', $css_classes );
	}
}