<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* This class takes care of all form related functionality
*/
class MC4WP_Lite_Form_Manager {

	/**
	 * @var array
	 */
	private $options = array();

	/**
	* @var int
	*/
	private $form_instance_number = 1;

	/**
	 * @var MC4WP_Form_Request|boolean
	 */
	private $form_request = false;

	/**
	 * @var bool Is the inline CSS printed already?
	 */
	private $inline_css_printed = false;

	/**
	 * @var bool Is the inline JavaScript printed to the page already?
	 */
	private $inline_js_printed = false;

	/**
	 * @var bool Whether to print the JS snippet "fixing" date fields
	 */
	private $print_date_fallback = false;

	/**
	* Constructor
	*/
	public function __construct() {
		add_action( 'init', array( $this, 'initialize' ) );
	}

	/**
	* Initializes the Form functionality
	*
	* - Registers scripts so developers can override them, should they want to.
	*/
	public function initialize() {

		$this->options = mc4wp_get_options( 'form' );

		$this->register_shortcodes();

		// has a MC4WP form been submitted?
		if ( isset( $_POST['_mc4wp_form_submit'] ) ) {
			$this->form_request = new MC4WP_Lite_Form_Request( $_POST );
		}

		// frontend only
		if( ! is_admin() ) {

			// load checkbox css if necessary
			add_action( 'wp_head', array( $this, 'print_css' ), 90 );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );

			$this->register_scripts();
		}

	}

	/**
	 * Registers the [mc4wp_form] shortcode
	 */
	protected function register_shortcodes() {
		// register shortcodes
		add_shortcode( 'mc4wp_form', array( $this, 'output_form' ) );

		// @deprecated, use [mc4wp_form] instead
		add_shortcode( 'mc4wp-form', array( $this, 'output_form' ) );

		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );
	}

	/**
	 * Register the various JS files used by the plugin
	 */
	protected function register_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// register placeholder script, which will later be enqueued for IE only
		wp_register_script( 'mc4wp-placeholders', MC4WP_LITE_PLUGIN_URL . 'assets/js/third-party/placeholders.min.js', array(), MC4WP_LITE_VERSION, true );

		// register non-AJAX script (that handles form submissions)
		wp_register_script( 'mc4wp-form-request', MC4WP_LITE_PLUGIN_URL . 'assets/js/form-request' . $suffix . '.js', array(), MC4WP_LITE_VERSION, true );
	}

	/**
	* Load the form stylesheet(s)
	*/
	public function load_stylesheet( ) {
		
		if( $this->options['css'] == false ) {
			return false;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if( $this->options['css'] != 1 && $this->options['css'] !== 'default' ) {

			$form_theme = $this->options['css'];
			if( in_array( $form_theme, array( 'blue', 'green', 'dark', 'light', 'red' ) ) ) {
				wp_enqueue_style( 'mailchimp-for-wp-form-theme-' . $this->options['css'], MC4WP_LITE_PLUGIN_URL . 'assets/css/form-theme-' . $this->options['css'] . $suffix . '.css', array(), MC4WP_LITE_VERSION, 'all' );
			}

		} else {
			wp_enqueue_style( 'mailchimp-for-wp-form', MC4WP_LITE_PLUGIN_URL . 'assets/css/form' . $suffix . '.css', array(), MC4WP_LITE_VERSION, 'all' );
		}

		return true;
	}

	/**
	* Gets CSS classess to add to the form element
	*
	* @return string
	*/
	private function get_css_classes() {

		/**
		 * @filter mc4wp_form_css_classes
		 * @expects array
		 *
		 * Can be used to add additional CSS classes to the form container
		 */
		$css_classes = apply_filters( 'mc4wp_form_css_classes', array( 'form' ) );

		// the following classes MUST be used
		$css_classes[] = 'mc4wp-form';

		// Add form classes if a Form Request was captured
		if( is_object( $this->form_request ) && $this->form_request->get_form_instance_number() === $this->form_instance_number ) {

			$css_classes[] = 'mc4wp-form-submitted';

			if( $this->form_request->is_successful() ) {
				$css_classes[] = 'mc4wp-form-success';
			} else {
				$css_classes[] = 'mc4wp-form-error';
			}

		}

		return implode( ' ', $css_classes );
	}

	/**
	* Returns the MailChimp for WP form mark-up
	*
	* @param array $atts
	* @param string $content
	*
	* @return string
	*/
	public function output_form( $atts = array(), $content = '' ) {

		// make sure template functions are loaded
		if ( ! function_exists( 'mc4wp_replace_variables' ) ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions/template.php';
		}

		// was this form submitted?
		$was_submitted = ( is_object( $this->form_request ) && $this->form_request->get_form_instance_number() === $this->form_instance_number );

		// Generate opening HTML
		$opening_html = '<!-- Form by MailChimp for WordPress plugin v'. MC4WP_LITE_VERSION .' - https://mc4wp.com/ -->';
		$opening_html .= '<div id="mc4wp-form-' . $this->form_instance_number . '" class="' . $this->get_css_classes() . '">';

		// Generate before & after fields HTML
		$before_form = apply_filters( 'mc4wp_form_before_form', '' );
		$after_form = apply_filters( 'mc4wp_form_after_form', '' );

		$form_opening_html = '';
		$form_closing_html = '';

		$visible_fields = '';
		$hidden_fields = '';

		$before_fields = apply_filters( 'mc4wp_form_before_fields', '' );
		$after_fields = apply_filters( 'mc4wp_form_after_fields', '' );

		// Process fields, if not submitted or not successfull or hide_after_success disabled
		if( ! $was_submitted || ! $this->options['hide_after_success'] || ! $this->form_request->is_successful() ) {

			$form_opening_html = '<form method="post">';
			$visible_fields = $this->get_visible_form_fields();

			// make sure to print date fallback later on if form contains a date field
			if( $this->form_contains_field_type( $visible_fields, 'date' ) ) {
				$this->print_date_fallback = true;
			}

			$hidden_fields = $this->get_hidden_form_fields();
			$form_closing_html = '</form>';
		}

		// empty string for response
		$response_html = '';

		if( $was_submitted ) {

			// Enqueue script (only after submit)
			wp_enqueue_script( 'mc4wp-form-request' );
			wp_localize_script( 'mc4wp-form-request', 'mc4wpFormRequestData', array(
					'success' => ( $this->form_request->is_successful() ) ? 1 : 0,
					'formId' => $this->form_request->get_form_instance_number(),
					'data' => $this->form_request->get_data()
				)
			);

			// get actual response html
			$response_html .= $this->form_request->get_response_html();

			// add form response after or before fields if no {response} tag
			if( stristr( $visible_fields, '{response}' ) === false || $this->options['hide_after_success']) {

				/**
				 * @filter mc4wp_form_message_position
				 * @expects string before|after
				 *
				 * Can be used to change the position of the form success & error messages.
				 * Valid options are 'before' or 'after'
				 */
				$message_position = apply_filters( 'mc4wp_form_message_position', 'after' );

				switch( $message_position ) {
					case 'before':
						$before_form = $before_form . $response_html;
						break;

					case 'after':
						$after_form = $response_html . $after_form;
						break;
				}
			}

		}

		// Always replace {response} tag, either with empty string or actual response
		$visible_fields = str_ireplace( '{response}', $response_html, $visible_fields );

		// Generate closing HTML
		$closing_html = '</div><!-- / MailChimp for WP Plugin -->';

		// increase form instance number in case there is more than one form on a page
		$this->form_instance_number++;

		// make sure scripts are enqueued later
		global $is_IE;
		if( isset( $is_IE ) && $is_IE ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		// Print small JS snippet later on in the footer.
		add_action( 'wp_footer', array( $this, 'print_js' ) );

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

		// print css to hide honeypot, if not printed already
		$this->print_css();

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * @return string
	 */
	private function get_visible_form_fields() {

		// add form fields from settings
		$visible_fields = __( $this->options['markup'], 'mailchimp-for-wp' );

		// replace special values
		$visible_fields = str_ireplace( array( '%N%', '{n}' ), $this->form_instance_number, $visible_fields );
		$visible_fields = mc4wp_replace_variables( $visible_fields, array_values( $this->options['lists'] ) );

		// insert captcha
		if( function_exists( 'cptch_display_captcha_custom' ) ) {
			$captcha_fields = '<input type="hidden" name="_mc4wp_has_captcha" value="1" /><input type="hidden" name="cntctfrm_contact_action" value="true" />' . cptch_display_captcha_custom();
			$visible_fields = str_ireplace( array( '{captcha}', '[captcha]' ), $captcha_fields, $visible_fields );
		}

		/**
		 * @filter mc4wp_form_content
		 * @param       int     $form_id    The ID of the form that is being shown
		 * @expects     string
		 *
		 * Can be used to customize the content of the form mark-up, eg adding additional fields.
		 */
		$visible_fields = apply_filters( 'mc4wp_form_content', $visible_fields, 0 );

		return (string) $visible_fields;
	}

	/**
	 * @return string
	 */
	private function get_hidden_form_fields() {
		$hidden_fields = '';
		$hidden_fields .= '<input type="text" name="_mc4wp_required_but_not_really" value="" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_timestamp" value="'. time() . '" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_submit" value="1" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_instance" value="'. $this->form_instance_number .'" />';
		$hidden_fields .= '<input type="hidden" name="_mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';
		return (string) $hidden_fields;
	}

	/**
	 * @param $form
	 * @param $field_type
	 *
	 * @return bool
	 */
	private function form_contains_field_type( $form, $field_type ) {
		$html = sprintf( ' type="%s" ', $field_type );
		return stristr( $form, $html ) !== false;
	}

	/**
	 * Prints some inline CSS that hides the honeypot field
	 *
	 * @return bool
	 */
	public function print_css() {

		if( $this->inline_css_printed ) {
			return false;
		}

		?><style type="text/css">.mc4wp-form input[name="_mc4wp_required_but_not_really"] { display: none !important; }</style><?php

		// make sure this function only runs once
		$this->inline_css_printed = true;
		return true;
	}

	/**
	 * Prints some JavaScript to enhance the form functionality
	 *
	 * This is only printed on pages that actually contain a form.
	 */
	public function print_js() {

		if( $this->inline_js_printed === true ) {
			return false;
		}

		// Print vanilla JavaScript
		?><script type="text/javascript">
			(function() {

				function addSubmittedClass() {
					var className = 'mc4wp-form-submitted';
					(this.classList) ? this.classList.add(className) : this.className += ' ' + className;
				}

				var forms = document.querySelectorAll('.mc4wp-form');
				for (var i = 0; i < forms.length; i++) {
					(function(f) {

						// hide honeypot
						var honeypot = f.querySelector('input[name="_mc4wp_required_but_not_really"]');
						honeypot.style.display = 'none';
						honeypot.setAttribute('type','hidden');

						// add class on submit
						var b = f.querySelector('[type="submit"]');
						if(b.addEventListener) {
							b.addEventListener( 'click', addSubmittedClass.bind(f));
						} else {
							b.attachEvent( 'onclick', addSubmittedClass.bind(f));
						}

					})(forms[i]);
				}
			})();

			<?php if( $this->print_date_fallback ) { ?>
			(function() {
				// test if browser supports date fields
				var testInput = document.createElement('input');
				testInput.setAttribute('type', 'date');
				if( testInput.type !== 'date') {

					// add placeholder & pattern to all date fields
					var dateFields = document.querySelectorAll('.mc4wp-form input[type="date"]');
					for(var i=0; i<dateFields.length; i++) {
						if(!dateFields[i].placeholder) {
							dateFields[i].placeholder = 'yyyy/mm/dd';
						}
						if(!dateFields[i].pattern) {
							dateFields[i].pattern = '(?:19|20)[0-9]{2}/(?:(?:0[1-9]|1[0-2])/(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])/(?:30))|(?:(?:0[13578]|1[02])-31))';
						}
					}
				}
			})();
			<?php } ?>
		</script><?php

		// make sure this function only runs once
		$this->inline_js_printed = true;
		return true;
	}

}
