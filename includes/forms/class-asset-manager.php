<?php

/**
* This class takes care of all form related functionality
 *
 * @access private
 * @ignore
*/
class MC4WP_Form_Asset_Manager {

	/**
	 * @var MC4WP_Form_Output_Manager|null
	 */
	protected $output_manager;

	/**
	 * @var bool
	 */
	protected $scripts_loaded = false;

	/**
	 * @var string
	 */
	protected $filename_suffix;

	/**
	 * Constructor
	 *
	 * @param MC4WP_Form_Output_Manager $output_manager
	 */
	public function __construct( MC4WP_Form_Output_Manager $output_manager = null ) {
		$this->output_manager = $output_manager;
		$this->filename_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	}

	/**
	 * Add hooks
	 */
	public function hook() {
		// load checkbox css if necessary
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheets' ) );
		add_action( 'mc4wp_output_form', array( $this, 'load_scripts' ) );
		add_action( 'wp_footer', array( $this, 'print_javascript' ), 999 );

		$this->register_assets();
	}

	/**
	 * Register the various JS files used by the plugin
	 *
	 * @deprecated 3.1.9
	 */
	public function register_assets() {
		$suffix = $this->filename_suffix;

		/**
		 * Runs right after all assets (scripts & stylesheets) for forms have been registered
		 *
		 * @since 3.0
		 * @deprecated 3.1.9
		 *
		 * @param string $suffix The suffix to add to the filename, before the file extension. Is usually set to ".min".
		 * @ignore
		 */
		do_action( 'mc4wp_register_form_assets', $suffix );
	}

	/**
	 * @param string $stylesheet
	 *
	 * @return bool
	 */
	public function is_registered_stylesheet( $stylesheet ) {
		$stylesheets = $this->get_registered_stylesheets();
		return in_array( $stylesheet, $stylesheets );
	}

	/**
	 * @return array
	 */
	public function get_registered_stylesheets() {
		return array(
			'basic',
			'themes'
		);
	}

	/**
	 * @param string $stylesheet
	 *
	 * @return string
	 */
	public function get_stylesheet_url( $stylesheet ) {
		if( ! $this->is_registered_stylesheet( $stylesheet ) ) {
			return '';
		}

		return MC4WP_PLUGIN_URL . 'assets/css/form-' . $stylesheet . $this->filename_suffix . '.css';
	}

	/**
	 * Get array of stylesheet handles which should be enqueued.
	 *
	 * @return array
	 */
	public function get_active_stylesheets() {
		$stylesheets = (array) get_option( 'mc4wp_form_stylesheets', array() );

		/**
		 * Filters the stylesheets to be loaded
		 *
		 * Should be an array of stylesheet handles previously registered using `wp_register_style`.
		 * Each value is prefixed with `mc4wp-form-` to get the handle.
		 *
		 * Return an empty array if you want to disable the loading of all stylesheets.
		 *
		 * @since 3.0
		 * @param array $stylesheets Array of valid stylesheet handles
		 */
		$stylesheets = (array) apply_filters( 'mc4wp_form_stylesheets', $stylesheets );
		return $stylesheets;
	}

	/**
	 * Load the various stylesheets
	 */
	public function load_stylesheets( ) {
		$stylesheets = $this->get_active_stylesheets();

		foreach( $stylesheets as $stylesheet ) {
			if( ! $this->is_registered_stylesheet( $stylesheet ) ) {
				continue;
			}

			$handle = 'mc4wp-form-' . $stylesheet;
			wp_enqueue_style( $handle, $this->get_stylesheet_url( $stylesheet ), array(), MC4WP_VERSION );
			add_editor_style( $this->get_stylesheet_url( $stylesheet ) );
		}

		/**
		 * @ignore
		 */
		do_action( 'mc4wp_load_form_stylesheets', $stylesheets );

		return true;
	}

	/**
	 * Get configuration object for client-side use.
	 *
	 * @return array
	 */
	public function get_javascript_config() {

		$submitted_form = mc4wp_get_submitted_form();

		if( ! $submitted_form ) {
			return array();
		}

		$config = array(
			'submitted_form' => array(
				'id' => $submitted_form->ID,
				'data' => $submitted_form->data,
				'action' => $submitted_form->config['action'],
				'element_id' => $submitted_form->config['element_id'],
			)
		);

		if( $submitted_form->has_errors() ) {
			$config['submitted_form']['errors'] = $submitted_form->errors;
		}

		$auto_scroll = 'default';

		/**
		 * Filters the `auto_scroll` setting for when a form is submitted.
		 *
		 * Accepts the following  values:
		 *
		 * - false
		 * - "default"
		 * - "animated"
		 *
		 * @param boolean|string $auto_scroll
		 * @since 3.0
		 */
		$config['auto_scroll'] = apply_filters( 'mc4wp_form_auto_scroll', $auto_scroll );

		return $config;
	}

	/**
	 * Load JavaScript files
	 * @return bool
	 */
	public function load_scripts() {
		global $wp_scripts;

		if( $this->scripts_loaded ) {
			return false;
		}

		// print dummy JS
		$this->print_dummy_javascript();

		// load API script
		wp_enqueue_script( 'mc4wp-forms-api', MC4WP_PLUGIN_URL . 'assets/js/forms-api'.  $this->filename_suffix .'.js', array(), MC4WP_VERSION, true );
		wp_localize_script( 'mc4wp-forms-api', 'mc4wp_forms_config', $this->get_javascript_config() );

		// load placeholder polyfill if browser is Internet Explorer
		wp_enqueue_script( 'mc4wp-forms-placeholders', MC4WP_PLUGIN_URL . 'assets/js/third-party/placeholders.min.js', array(), MC4WP_VERSION, true );
		$wp_scripts->add_data( 'mc4wp-forms-placeholders', 'conditional', 'lte IE 9' );

		/**
		 * @ignore
		 */
		do_action( 'mc4wp_load_form_scripts' );

		$this->scripts_loaded = true;
		return true;
	}

	/**
	 * Prints dummy JavaScript which allows people to call `mc4wp.forms.on()` before the JS is loaded.
	 */
	public function print_dummy_javascript() {
		$file = dirname( __FILE__ ) . '/views/js/dummy-api.js';
		echo '<script type="text/javascript">';
		include $file;
		echo '</script>';
	}

	/**
	* Outputs the inline JavaScript that is used to enhance forms
	*/
	public function print_javascript() {

		// don't print any scripts if this page has no forms
		if( ! $this->output_manager || empty( $this->output_manager->printed_forms ) ) {
			return false;
		}

		// make sure scripts are loaded
		$this->load_scripts();

		// print inline scripts depending on printed fields
		echo '<script type="text/javascript">';
		echo '(function() {';

		// include general form enhancements
		include  dirname( __FILE__ ) . '/views/js/general-form-enhancements.js';

		// include url fix
		if( in_array( 'url', $this->output_manager->printed_field_types ) ) {
			include dirname( __FILE__ ) . '/views/js/url-fields.js';
		}

		// include date polyfill?
		if( in_array( 'date', $this->output_manager->printed_field_types ) ) {
			include dirname( __FILE__ ) . '/views/js/date-fields.js';
		}

		echo '})();';
		echo '</script>';

		/**
		 * Runs right after inline JavaScript is printed, just before the closing </body> tag.
		 *
		 * This function will only run if the current page contains at least one form.
		 *
		 * @ignore
		 */
		do_action( 'mc4wp_print_forms_javascript' );
	}




}
