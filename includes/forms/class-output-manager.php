<?php


/**
 * Class MC4WP_Form_Output_Manager
 *
 * @ignore
 * @access private
 */
class MC4WP_Form_Output_Manager {

	/**
	 * @var int
	 */
	public $count = 0;

	/**
	 * @var MC4WP_Form[]
	 */
	public $printed_forms = array();

	/**
	 * @var array
	 */
	public $printed_field_types = array();

	/**
	 * @const string
	 */
	const SHORTCODE = 'mc4wp_form';

	/**
	 * @var array
	 */
	protected $shortcode_attributes = array(
		'id' => '',
		'lists' => '',
		'email_type' => '',
		'element_id' => '',
        'element_class' => '',
	);

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 *
	 */
	public function add_hooks() {
		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

		// enable shortcodes in form content
		add_filter( 'mc4wp_form_content', 'do_shortcode' );

		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Registers the [mc4wp_form] shortcode
	 */
	public function register_shortcode() {
		// register shortcodes
		add_shortcode( self::SHORTCODE, array( $this, 'shortcode' ) );
	}

	/**
	 * @param array  $attributes
	 * @param string $content
	 * @return string
	 */
	public function shortcode( $attributes = array(), $content = '' ) {

		$attributes = shortcode_atts(
			$this->shortcode_attributes,
			$attributes,
			self::SHORTCODE
		);

		$config = $attributes;
		unset( $config['id'] );

		return $this->output_form( $attributes['id'], $config, false );
	}

	/**
	 * @param int   $id
	 * @param array $config
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function output_form( $id = 0, $config = array(), $echo = true ) {

		try {
			$form = mc4wp_get_form( $id );
		} catch( Exception $e ) {

			if( current_user_can( 'manage_options' ) ) {
				return sprintf( '<strong>MailChimp for WordPress error:</strong> %s', $e->getMessage() );
			}

			return '';
		}

		$this->count++;

        // set a default element_id if none is given
		if( empty( $config['element_id'] ) ) {
			$config['element_id'] = 'mc4wp-form-' . $this->count;
		}

		$this->printed_forms[ $form->ID ] = $form;
		$this->printed_field_types += $form->get_field_types();
		$this->printed_field_types = array_unique( $this->printed_field_types );

		// start new output buffer
		ob_start();

		/**
		 * Runs just before a form element is outputted.
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Form $form
		 */
		do_action( 'mc4wp_output_form', $form );

		// output the form (in output buffer)
		echo $form->get_html( $config['element_id'], $config );

		// grab all contents in current output buffer & then clean it.
		$html = ob_get_clean();

		// echo content if necessary
		if( $echo ) {
			echo $html;
		}

		return $html;
	}

}