<?php

/**
* This class takes care of all form related functionality
 *
 * @access private
 * @ignore
*/
class MC4WP_Form_Asset_Manager {

	/**
	 * @var bool
	 */
	protected $load_scripts = false;

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'mc4wp_output_form', array( $this, 'before_output_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheets' ) );
		add_action( 'wp_footer', array( $this, 'load_scripts' ) );
	}

	/**
	 * @param string $stylesheet
	 *
	 * @return bool
	 */
	public function is_registered_stylesheet( $stylesheet ) {
		$stylesheets = $this->get_registered_stylesheets();
		return in_array( $stylesheet, $stylesheets, true );
	}

	/**
	 * @return array
	 */
	public function get_registered_stylesheets() {
		return array(
			'basic',
			'themes',
		);
	}

	/**
	 * @param string $stylesheet
	 *
	 * @return string
	 */
	public function get_stylesheet_url( $stylesheet ) {
		if ( ! $this->is_registered_stylesheet( $stylesheet ) ) {
			return '';
		}

		return MC4WP_PLUGIN_URL . 'assets/css/form-' . $stylesheet . '.min.css';
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
	public function load_stylesheets() {
		$stylesheets = $this->get_active_stylesheets();

		foreach ( $stylesheets as $stylesheet ) {
			if ( ! $this->is_registered_stylesheet( $stylesheet ) ) {
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
	 * Get data object for client-side use for after a form is submitted over HTTP POST (not AJAX).
	 *
	 * @return array
	 */
	public function get_submitted_form_data() {
		$submitted_form = mc4wp_get_submitted_form();
		if ( ! $submitted_form instanceof MC4WP_Form ) {
			return null;
		}

		$data = array(
			'id'         => $submitted_form->ID,
			'event'      => $submitted_form->last_event,
			'data'       => $submitted_form->get_data(),
			'element_id' => $submitted_form->config['element_id'],
			'auto_scroll' => true,
		);

		if ( $submitted_form->has_errors() ) {
			$data['errors'] = $submitted_form->errors;
		}

		/**
		 * Filters the `auto_scroll` setting for when a form is submitted.
		 * Set to false to disable scrolling to form.
		 *
		 * @param boolean $auto_scroll
		 * @since 3.0
		 */
		$data['auto_scroll'] = apply_filters( 'mc4wp_form_auto_scroll', $data['auto_scroll'] );

		return $data;
	}

	/**
	 * Load JavaScript files
	 */
	public function before_output_form() {
		// print dummy JS
		$this->print_dummy_javascript();

		// set flag to load JS files in wp_footer
		$this->load_scripts = true;
	}

	/**
	 * Prints dummy JavaScript which allows people to call `mc4wp.forms.on()` before the JS is loaded.
	 */
	public function print_dummy_javascript() {
		$file = dirname( __FILE__ ) . '/views/js/dummy-api.js';
		echo '<script>';
		include $file;
		echo '</script>';
	}

	/**
	* Outputs the inline JavaScript that is used to enhance forms
	*/
	public function load_scripts() {
		$load_scripts = $this->load_scripts;

		/** @ignore */
		$load_scripts = apply_filters( 'mc4wp_load_form_scripts', $load_scripts );
		if ( ! $load_scripts ) {
			return;
		}

		$filename_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// load general client-side form API
		wp_enqueue_script( 'mc4wp-forms-api', MC4WP_PLUGIN_URL . 'assets/js/forms' . $filename_suffix . '.js', array(), MC4WP_VERSION, true );

		// maybe load JS file for when a form was submitted over HTTP POST
		$submitted_form_data = $this->get_submitted_form_data();
		if ( $submitted_form_data !== null ) {
			wp_enqueue_script( 'mc4wp-forms-submitted', MC4WP_PLUGIN_URL . 'assets/js/forms-submitted' . $filename_suffix . '.js', array( 'mc4wp-forms-api' ), MC4WP_VERSION, true );
			wp_localize_script( 'mc4wp-forms-submitted', 'mc4wp_submitted_form', $submitted_form_data );
		}

		// print inline scripts
		echo '<script>';
		echo '(function() {';
		include dirname( __FILE__ ) . '/views/js/url-fields.js';
		echo '})();';
		echo '</script>';

		/** @ignore */
		do_action( 'mc4wp_load_form_scripts' );
	}
}
