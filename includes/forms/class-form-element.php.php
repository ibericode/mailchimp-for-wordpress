<?php

/**
 * Class MC4WP_Form_Element
 *
 * @since 3.0
 * @ignore
 * @access private
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
		                      && $this->form->config['element_id'] == $this->ID;
	}


	/**
	 * @return string
	 */
	public function get_visible_fields() {

		$content = $this->form->content;
		$form = $this->form;

		/**
		 * Filters the HTML for the form fields.
		 *
		 * Use this filter to add custom HTML to a form programmatically
		 *
		 * @param string $content
		 * @param MC4WP_Form $form
		 * @since 2.0
		 */
		$visible_fields = (string) apply_filters( 'mc4wp_form_content', $content, $form );

		return $visible_fields;
	}

	/**
	 * @return string
	 */
	public function get_hidden_fields() {

		// hidden fields
		$hidden_fields = '<div style="display: none;"><input type="text" name="_mc4wp_honeypot" value="" tabindex="-1" autocomplete="off" autofill="off" /></div>';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_timestamp" value="'. time() . '" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_id" value="'. esc_attr( $this->form->ID ) .'" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_element_id" value="'. esc_attr( $this->ID ) .'" />';

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

		$position = 'after';
		$form = $this->form;

		// check if content contains {response} tag
		if( stripos( $this->form->content, '{response}' ) !== false ) {
			return '';
		}

		/**
		 * Filters the position for the form response.
		 *
		 * Valid values are "before" and "after". Will have no effect if `{response}` is used in the form content.
		 *
		 * @param string $position
		 * @param MC4WP_Form $form
		 * @since 2.0
		 */
		$response_position = (string) apply_filters( 'mc4wp_form_response_position', $position, $form );

		return $response_position;
	}

	/**
	 * Get HTML to be added _before_ the HTML of the form fields.
	 *
	 * @return string
	 */
	protected function get_html_before_fields() {

		$html = '';
		$form = $this->form;

		/**
		 * Filters the HTML before the form fields.
		 *
		 * @param string $html
		 * @param MC4WP_Form $form
		 */
		$html = (string) apply_filters( 'mc4wp_form_before_fields', $html, $form );

		if( $this->get_response_position() === 'before' ) {
			$html = $html . $this->form->get_response_html( $this->is_submitted );
		}

		return $html;
	}

	/**
	 * Get HTML to be added _after_ the HTML of the form fields.
	 *
	 * @return string
	 */
	protected function get_html_after_fields() {

		$html = '';
		$form = $this->form;

		/**
		 * Filters the HTML after the form fields.
		 *
		 * @param string $html
		 * @param MC4WP_Form $form
		 */
		$html = (string) apply_filters( 'mc4wp_form_after_fields', $html, $form );

		if( $this->get_response_position() === 'after' ) {
			$html = $this->form->get_response_html( $this->is_submitted ) . $html;
		}

		return $html;
	}

	/**
	 * Get all HTMl attributes for the form element
	 *
	 * @return string
	 */
	protected function get_form_element_attributes() {

		$form = $this;
		$form_action_attribute = null;

		$attributes = array(
			'id' => $this->ID,
			'class' => $this->get_css_classes()
		);

		/**
		 * Filters the `action` attribute of the `<form>` element.
		 *
		 * Defaults to `null`, which means no `action` attribute will be printed.
		 *
		 * @param string $form_action_attribute
		 * @param MC4WP_Form $form
		 */
		$form_action_attribute = apply_filters( 'mc4wp_form_action', $form_action_attribute, $form );
		if( is_string( $form_action_attribute ) ) {
			$attributes['action'] = $form_action_attribute;
		}

		/**
		 * Filters all attributes to be added to the `<form>` element
		 *
		 * @param array $attributes Key-value pairs of attributes.
		 * @param MC4WP_Form $form
		 */
		$attributes = (array) apply_filters( 'mc4wp_form_element_attributes', $attributes, $form );

		// hardcoded attributes, can not be changed.
		$attributes['method'] = 'post';
		$attributes['data-id'] = $this->form->ID;
		$attributes['data-name'] = $this->form->name;

		// build string of key="value" from array
		$string = '';
		foreach( $attributes as $name => $value ) {
			$string .= sprintf( '%s="%s" ', $name, esc_attr( $value ) );
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

		// Start building content string
		$opening_html = '<!-- MailChimp for WordPress v' . MC4WP_VERSION . ' - https://wordpress.org/plugins/mailchimp-for-wp/ -->';
		$opening_html .= '<form '. $this->get_form_element_attributes() .'>';
		$before_fields = $this->get_html_before_fields();
		$fields = '';
		$after_fields = $this->get_html_after_fields();
		$closing_html = '</form><!-- / MailChimp for WordPress Plugin -->';

		if( ! $this->is_submitted
		    || ! $this->form->settings['hide_after_success']
			|| $this->form->has_errors()) {

			// add HTML for fields + wrapper element.
			$fields = '<div class="mc4wp-form-fields">' .
			            $this->get_visible_fields() .
			            $this->get_hidden_fields() .
						'</div>';
		}

		// concatenate everything
		$output = $opening_html .
		          $before_fields .
		          $fields .
		          $after_fields .
		          $closing_html;

		return $output;
	}

	/**
	 * Get a space separated list of CSS classes for this form
	 *
	 * @return string
	 */
	public function get_css_classes() {

		$classes = array();
		$form = $this->form;

		$classes[] = 'mc4wp-form';
		$classes[] = 'mc4wp-form-' . $form->ID;

		// Add form classes if this specific form element was submitted
		if( $this->is_submitted ) {
			$classes[] = 'mc4wp-form-submitted';

			if( ! $form->has_errors() ) {
				$classes[] = 'mc4wp-form-success';
			} else {
				$classes[] = 'mc4wp-form-error';
			}
		}

		// add class for CSS targeting in custom stylesheets
		if( ! empty( $form->settings['css'] ) ) {

			if( strpos( $form->settings['css'], 'theme-' ) === 0 ) {
				$classes[] = 'mc4wp-form-theme';
			}

			$classes[] = 'mc4wp-form-' . $form->settings['css'];
		}

		/**
		 * Filters `class` attributes for the `<form>` element.
		 *
		 * @param array $classes
		 * @param MC4WP_Form $form
		 */
		$classes = apply_filters( 'mc4wp_form_css_classes', $classes, $form );

		return implode( ' ', $classes );
	}
}