<?php

/**
* This class takes care of all form related functionality
*/
abstract class MC4WP_Forms_Manager_Base {

	/**
	 * @var array
	 */
	public $options = array();

	/**
	* @var int
	*/
	protected $outputted_forms_count = 0;

	/**
	 * @var MC4WP_Forms_Assets
	 */
	public $assets;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->options = $this->load_options();
	}

	/**
	 * Init all form related functionality
	 */
	public function init() {
		$this->add_hooks();
	}

	/**
	 * Adds the necessary hooks
	 */
	protected function add_hooks() {
		// load checkbox css if necessary
		add_action( 'init', array( $this, 'init_listener' ) );
		add_action( 'template_redirect', array( $this, 'init_assets') );
	}

	/**
	 * Initialise form assets
	 */
	public function init_assets() {
		$this->assets = new MC4WP_Forms_Assets( $this->options );
		$this->assets->add_hooks();
	}

	/**
	 * Initialise the form listener
	 *
	 * @hooked `init`
	 */
	public function init_listener() {
		$listener = new MC4WP_Forms_Listener();
		$listener->listen( $_POST );
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
		$form = MC4WP_Form::get( $attributes['id'] );

		// did we find a valid form with this ID?
		if( ! $form ) {

			if( current_user_can( 'manage_options' ) ) {
				return '<p>'. __( '<strong>Error:</strong> Sign-up form not found. Please check if you used the correct form ID.', 'mailchimp-for-wp' ) .'</p>';
			}

			return '';
		}

		// make sure to print date fallback later on if form contains a date field
		if( $form->contains_field_type( 'date' ) ) {
			$this->assets->print_date_fallback = true;
		}

		// was form submited?
		if( $form->is_submitted( $attributes['element_id'] ) ) {

			// enqueue scripts (in footer) if form was submited
			wp_enqueue_script( 'mc4wp-form-request' );
			wp_localize_script( 'mc4wp-form-request', 'mc4wpFormRequestData', array(
					'success' => ( $form->request->success ) ? 1 : 0,
					'formElementId' => $form->request->config['form_element_id'],
					'data' => $form->request->data,
				)
			);

		}

		// make sure scripts are enqueued later
		global $is_IE;
		if( isset( $is_IE ) && $is_IE ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		// tell asset manager to print JavaScript snippet
		$this->assets->print_js();

		// Print CSS to hide honeypot (should be printed in `wp_head` by now)
		$html = '';

		// add inline css if it was not printed yet
		$html .= $this->assets->print_css( false );

		// output form
		$html .= $form->output( $attributes['element_id'], $attributes, false );

		return $html;
	}

	/**
	 * @return array
	 */
	public function load_options() {
		$options = (array) get_option( 'mc4wp_form', array() );
		$defaults = include MC4WP_PLUGIN_DIR . '/config/default-options.php';
		$options = array_merge( $defaults['form'], $options );
		return $options;
	}

}
