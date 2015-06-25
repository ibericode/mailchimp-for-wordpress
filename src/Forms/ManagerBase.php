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
	 * Constructor
	 */
	public function __construct() {
		$this->options = $this->load_options();
	}

	/**
	 * @param $name
	 *
	 * @return MC4WP_Forms_Assets|null
	 */
	public function __get( $name ) {

		if( $name === 'assets' ) {
			$this->assets = new MC4WP_Forms_Assets( $this->options );
			return $this->assets;
		}

		return null;
	}

	/**
	 * Init all form related functionality
	 */
	public function init() {
		$this->add_hooks();
		$this->register_shortcodes();
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
	 * Registers the [mc4wp_form] shortcode
	 */
	protected function register_shortcodes() {
		// register shortcodes
		add_shortcode( 'mc4wp_form', array( $this, 'output_form' ) );

		// @deprecated, use [mc4wp_form] instead
		add_shortcode( 'mc4wp-form', array( $this, 'output_form' ) );
	}

	/**
	 * Initialise form assets
	 * @hooked `template_redirect`
	 */
	public function init_assets() {
		$this->assets->init();
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

		// parse shortcode attributes (sets up defaults too)
		$attributes = $this->parse_shortcode_attributes( $attributes );

		// create or retrieve form instance
		$form = MC4WP_Form::get( $attributes['id'] );

		// did we find a valid form with this ID?
		if( ! $form ) {

			if( current_user_can( 'manage_options' ) ) {
				return '<p>'. __( '<strong>Error:</strong> Sign-up form not found. Please check if you used the correct form ID.', 'mailchimp-for-wp' ) .'</p>';
			}

			return '';
		}

		// tell asset manager to print assets for this form
		$this->assets->print_form_assets( $form );

		// add inline css to form output if it was not printed yet
		$content .= $this->assets->print_css( false );

		// output form
		$content .= $form->output( $attributes['element_id'], $attributes, false );

		return $content;
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

	/**
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function parse_shortcode_attributes( array $attributes ) {

		$defaults = array(
			'id' => 0,
			'element_id' => 'mc4wp-form-' . $this->outputted_forms_count,
		);

		return shortcode_atts(
			$defaults,
			$attributes,
			'mc4wp_form'
		);
	}

}