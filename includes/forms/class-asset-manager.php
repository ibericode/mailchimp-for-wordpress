<?php

/**
* This class takes care of all form related functionality
 * @internal
*/
class MC4WP_Form_Asset_Manager {

	/**
	 * @var MC4WP_Form_Output_Manager
	 */
	private $output_manager;

	/**
	 * Constructor
	 *
	 * @param MC4WP_Form_Output_Manager $output_manager
	 */
	public function __construct( MC4WP_Form_Output_Manager $output_manager ) {
		$this->output_manager = $output_manager;
	}

	/**
	 * Init all form related functionality
	 */
	public function initialize() {
		$this->add_hooks();
		$this->register_scripts();
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// load checkbox css if necessary
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheets' ) );
		add_action( 'wp_footer', array( $this, 'print_javascript' ), 9999 );
	}

	/**
	 * Register the various JS files used by the plugin
	 */
	public function register_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// register placeholder script, which will later be enqueued for IE only
		wp_register_script( 'mc4wp-placeholders', MC4WP_PLUGIN_URL . 'assets/js/third-party/placeholders.min.js', array(), MC4WP_VERSION, true );

		// register non-AJAX script (that handles form submissions)
		wp_register_script( 'mc4wp-form-request', MC4WP_PLUGIN_URL . 'assets/js/form-request' . $suffix . '.js', array(), MC4WP_VERSION, true );

		do_action( 'mc4wp_form_register_scripts', $suffix );
	}

	/**
	 * Load the various stylesheets
	 */
	public function load_stylesheets( ) {

		$stylesheets = (array) get_option( 'mc4wp_form_stylesheets', array() );
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		foreach( $stylesheets as $stylesheet ) {
			$handle = 'mailchimp-for-wp-form-' . $stylesheet;
			$src = 'assets/css/' . $stylesheet . $suffix . '.css';

			// check if it exists, 404 is more expensive than filesystem check
			if( file_exists( MC4WP_PLUGIN_DIR . $src ) ) {
				wp_enqueue_style( $handle, MC4WP_PLUGIN_URL . $src, array(), MC4WP_VERSION );
			}
		}

		return true;
	}

	/**
	* Returns the MailChimp for WP form mark-up
	*
	* @return string
	*/
	public function print_javascript() {


		// make sure scripts are enqueued later
		if( ! empty( $GLOBALS['is_IE'] ) ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		// Print vanilla JavaScript
		echo '<script type="text/javascript">';
		echo '(function() {';

		// include general form enhancements
		include MC4WP_PLUGIN_DIR . 'includes/views/js/general-form-enhancements.js';

		// include url fix
		if( in_array( 'url', $this->output_manager->printed_field_types ) ) {
			include MC4WP_PLUGIN_DIR . 'includes/views/js/url-fields.js';
		}

		// include date polyfill?
		if( in_array( 'date', $this->output_manager->printed_field_types ) ) {
			include MC4WP_PLUGIN_DIR . 'includes/views/js/date-fields.js';
		}

		echo '})();';
		echo '</script>';



		// was any of the printed forms submitted?
//		if( $form->is_submitted( $attributes['element_id'] ) ) {
//
//			// enqueue scripts (in footer) if form was submitted
//			$animate_scroll = apply_filters( 'mc4wp_form_animate_scroll', true );
//
//			wp_enqueue_script( 'mc4wp-form-request' );
//			wp_localize_script( 'mc4wp-form-request', 'mc4wpFormRequestData', array(
//					'success' => ( $form->request->success ) ? 1 : 0,
//					'formElementId' => $form->request->form_element_id,
//					'data' => $form->request->user_data,
//					'animate_scroll' => $animate_scroll
//				)
//			);
//
//		}



	}


}
