<?php

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
	private $outputted_forms_count = 0;

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
		$this->options = mc4wp_get_options( 'form' );
	}

	/**
	 * Init all form related functionality
	 */
	public function init() {
		$this->add_hooks();
		$this->register_scripts();
		$this->register_shortcodes();
	}


	public function add_hooks() {
		// load checkbox css if necessary
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );

		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

		// enable shortcodes in form content
		add_filter( 'mc4wp_form_content', 'do_shortcode' );
	}

	/**
	 * Registers the [mc4wp_form] shortcode
	 */
	protected function register_shortcodes() {
		// register shortcodes
		add_shortcode( 'mc4wp_form', array( $this, 'output_form' ) );

		// @deprecated, use [mc4wp_form] instead
		add_shortcode( 'mc4wp-form', array( $this, 'output_form' ) );
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

		if ( ! $this->options['css'] ) {
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
	* Returns the MailChimp for WP form mark-up
	*
	* @param array $attributes
	* @param string $content
	*
	* @return string
	*/
	public function output_form( $attributes = array(), $content = '' ) {

		global $is_IE;

		// increase count of outputted forms
		$this->outputted_forms_count++;

		$attributes = shortcode_atts(
			array(
				'id' => 0,
				'element_id' => 'mc4wp-form-' . $this->outputted_forms_count,
			),
			$attributes,
			'mc4wp_form'
		);

		// create or retrieve form instance
		$form = MC4WP_Form::get();

		// make sure to print date fallback later on if form contains a date field
		if( $form->contains_field_type( 'date' ) ) {
			$this->print_date_fallback = true;
		}

		// was form submited?
		if( $form->is_submitted( $attributes['element_id'] ) ) {

			// enqueue scripts (in footer) if form was submitted
			$animate_scroll = apply_filters( 'mc4wp_form_animate_scroll', true );

			wp_enqueue_script( 'mc4wp-form-request' );
			wp_localize_script( 'mc4wp-form-request', 'mc4wpFormRequestData', array(
					'success' => ( $form->request->success ) ? 1 : 0,
					'formElementId' => $form->request->form_element_id,
					'data' => $form->request->user_data,
					'animate_scroll' => $animate_scroll
				)
			);

		}

		// Print small JS snippet later on in the footer.
		add_action( 'wp_footer', array( $this, 'print_js' ), 99 );

		// make sure scripts are enqueued later
		if( isset( $is_IE ) && $is_IE ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		// output form
		return $form->output( $attributes['element_id'], $attributes, false );
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
		echo '<script type="text/javascript">';

		// include general form enhancements
		include MC4WP_LITE_PLUGIN_DIR . 'includes/views/parts/form-enhancements.js';

		// include date polyfill?
		if( $this->print_date_fallback ) {
			include MC4WP_LITE_PLUGIN_DIR . 'includes/views/parts/date-polyfill.js';
		}

		echo '</script>';

		// make sure this function only runs once
		$this->inline_js_printed = true;
		return true;
	}

}
