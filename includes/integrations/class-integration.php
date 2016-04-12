<?php

/**
 * Class MC4WP_Integration
 *
 * Base class for all integrations.
 *
 * Extend this class and implement the `add_hooks` method to get a settings page.
 *
 * @access public
 * @since 3.0
 * @abstract
 */
abstract class MC4WP_Integration {

	/**
	 * @var string Name of this integration.
	 */
	public $name = '';

	/**
	 * @var string Description
	 */
	public $description = '';

	/**
	 * @var string Slug, used as an unique identifier for this integration.
	 */
	public $slug = '';

	/**
	 * @var array Array of settings
	 */
	public $options = array();

	/**
	 * @var string Name attribute for the checkbox element. Will be created from slug if empty.
	 */
	protected $checkbox_name = '';

	/**
	 * Constructor
	 *
	 * @param string $slug
	 * @param array $options
	 */
	public function __construct( $slug, array $options ) {
		$this->slug = $slug;
		$this->options = $this->parse_options( $options );

		// if checkbox name is not set, set a good custom value
		if( empty( $this->checkbox_name ) ) {
			$this->checkbox_name = '_mc4wp_subscribe_' . $this->slug;
		}
	}

	/**
	 * Return array of default options
	 *
	 * @staticvar $defaults
	 * @return array
	 */
	protected function get_default_options() {
		static $defaults;

		if( ! $defaults ) {
			$defaults = require MC4WP_PLUGIN_DIR . 'config/default-integration-options.php';
		}

		return $defaults;
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	protected function parse_options( array $options ) {
		$slug = $this->slug;

		$options = array_merge( $this->get_default_options(), $options );

		/**
		 * Filters options for a specific integration
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the slug of the ingration.
		 *
		 * @param array $integration_options
		 */
		return (array) apply_filters( 'mc4wp_' . $slug . '_integration_options', $options );
	}

	/**
	 * Initialize the integration
	 */
	public function initialize() {
		$this->add_required_hooks();
		$this->add_hooks();
	}

	/**
	 * Adds the required hooks for core functionality, like adding checkbox reset CSS.
	 */
	protected function add_required_hooks() {
		if( $this->options['css'] && ! $this->options['implicit'] ) {
			add_action( 'wp_head', array( $this, 'print_css_reset' ) );
		}
	}

	/**
	 * Was integration triggered?
	 *
	 * Will always return true when integration is implicit. Otherwise, will check value of checkbox.
	 *
	 * @param int $object_id Useful when overriding method. (optional)
	 * @return bool
	 */
	public function triggered( $object_id = null ) {
		return $this->options['implicit'] || $this->checkbox_was_checked();
	}

	/**
	 * Adds the hooks which are specific to this integration
	 */
	abstract protected function add_hooks();

	/**
	 * Print CSS reset
	 *
	 * @hooked `wp_head`
	 */
	public function print_css_reset() {
		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';
		$css = file_get_contents( MC4WP_PLUGIN_DIR . 'assets/css/checkbox-reset' . $suffix . '.css' );

		// replace selector by integration specific selector so the css affects just this checkbox
		$css = str_ireplace( '__INTEGRATION_SLUG__', $this->slug, $css );

		printf( '<style type="text/css">%s</style>', $css );
	}

	/**
	 * Get the text for the label element
	 *
	 * @return string
	 */
	public function get_label_text() {
		$integration = $this;
		$label = $this->options['label'];

		/**
		 * Filters the checkbox label
		 *
		 * @since 3.0
		 *
		 * @param string $label
		 * @param MC4WP_Integration $integration
		 */
		$label = (string) apply_filters( 'mc4wp_integration_checkbox_label', $label, $integration );
		return $label;
	}

	/**
	 * Was the integration checkbox checked?
	 *
	 * @return bool
	 */
	public function checkbox_was_checked() {
		$data = $this->get_data();
		return ( isset( $data[ $this->checkbox_name ] ) && $data[ $this->checkbox_name ] == 1 );
	}

	/**
	 * Get a string of attributes for the checkbox element.
	 *
	 * @return string
	 */
	protected function get_checkbox_attributes() {

		$integration = $this;
		$slug = $this->slug;

		$attributes = array();

		if( $this->options['precheck'] ) {
			$attributes['checked'] = 'checked';
		}

		/**
		 * Filters the attributes array.
		 *
		 * @param array $attributes
		 * @param MC4WP_Integration $integration
		 */
		$attributes = (array) apply_filters( 'mc4wp_integration_checkbox_attributes', $attributes, $integration );

		/**
		 * Filters the attributes array.
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the slug for this integration.
		 *
		 * @param array $attributes
		 * @param MC4WP_Integration $integration
		 */
		$attributes = (array) apply_filters( 'mc4wp_integration_' . $slug . '_checkbox_attributes', $attributes, $integration );

		$string = '';
		foreach( $attributes as $key => $value ) {
			$string .= sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}

		return $string;
	}

	/**
	 * Outputs a checkbox
	 */
	public function output_checkbox() {
		echo $this->get_checkbox_html();
	}

	/**
	 * Get HTML for the checkbox
	 *
	 * @return string
	 */
	public function get_checkbox_html() {

		ob_start();
		?>
		<!-- MailChimp for WordPress v<?php echo MC4WP_VERSION; ?> - https://mc4wp.com/ -->

		<?php do_action( 'mc4wp_integration_before_checkbox_wrapper', $this ); ?>
		<?php do_action( 'mc4wp_integration_'. $this->slug .'_before_checkbox_wrapper', $this ); ?>

		<p class="mc4wp-checkbox mc4wp-checkbox-<?php echo esc_attr( $this->slug ); ?>">
			<label>
				<?php // Hidden field to make sure "0" is sent to server ?>
				<input type="hidden" name="<?php echo esc_attr( $this->checkbox_name ); ?>" value="0" />
				<input type="checkbox" name="<?php echo esc_attr( $this->checkbox_name ); ?>" value="1" <?php echo $this->get_checkbox_attributes(); ?> />
				<span><?php echo $this->get_label_text(); ?></span>
			</label>
		</p>

		<?php do_action( 'mc4wp_integration_after_checkbox_wrapper', $this ); ?>
		<?php do_action( 'mc4wp_integration_'. $this->slug .'_after_checkbox_wrapper', $this ); ?>

		<!-- / MailChimp for WordPress -->
		<?php
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Get the selected MailChimp lists
	 *
	 * @return array Array of List ID's
	 */
	public function get_lists() {

		$data = $this->get_data();
		$integration = $this;
		$slug = $this->slug;

		// get checkbox lists options
		$lists = $this->options['lists'];

		// get lists from request, if set.
		if( ! empty( $data['_mc4wp_lists'] ) ) {

			$lists = $data['_mc4wp_lists'];

			// ensure lists is an array
			if( ! is_array( $lists ) ) {
				$lists = explode( ',', $lists );
				$lists = array_map( 'trim', $lists );
			}
		}

		// allow plugins to filter final lists value

		/**
		 * This filter is documented elsewhere.
		 *
		 * @since 2.0
		 * @see MC4WP_Form::get_lists
		 * @ignore
		 */
		$lists = (array) apply_filters( 'mc4wp_lists', $lists );

		/**
		 * Filters the MailChimp lists this integration should subscribe to
		 *
		 * @since 3.0
		 *
		 * @param array $lists
		 * @param MC4WP_Integration $integration
		 */
		$lists = (array) apply_filters( 'mc4wp_integration_lists', $lists, $integration );

		/**
		 * Filters the MailChimp lists a specific integration should subscribe to
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the slug of the integration.
		 *
		 * @since 3.0
		 *
		 * @param array $lists
		 * @param MC4WP_Integration $integration
		 */
		$lists = (array) apply_filters( 'mc4wp_integration_' . $slug . '_lists', $lists, $integration );

		return $lists;
	}

	/**
	 * Makes a subscription request
	 *
	 * @param string $email
	 * @param array $merge_vars
	 * @param int $related_object_id
	 * @return string|boolean
	 */
	protected function subscribe( $email, array $merge_vars = array(), $related_object_id = 0 ) {

		$integration = $this;
		$slug = $this->slug;

		/**
		 * @var MC4WP_API $api
		 */
		$api = mc4wp('api');
		$lists = $this->get_lists();
		$result = false;

		// validate lists
		if( empty( $lists ) ) {
			$this->get_log()->warning( sprintf( '%s > No MailChimp lists were selected', $this->name ) );
			return false;
		}

		/**
		 * Filters the final merge variables before the request is sent to MailChimp, for all integrations.
		 *
		 * @param array $merge_vars
		 * @param MC4WP_Integration $integration
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_integration_merge_vars', $merge_vars, $integration );

		/**
		 * Filters the final merge variables before the request is sent to MailChimp, for a specific integration.
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the integration slug.
		 *
		 * @param array $merge_vars
		 * @param MC4WP_Integration $integration
		 */
		$merge_vars = (array) apply_filters( 'mc4wp_integration_' . $slug . '_merge_vars', $merge_vars, $integration );
		$email_type = mc4wp_get_email_type();

		// create field map
		$map = new MC4WP_Field_Map( $merge_vars, $lists );

		foreach( $map->list_fields as $list_id => $list_field_data ) {
			$result = $api->subscribe( $list_id, $email, $list_field_data, $email_type, $this->options['double_optin'], $this->options['update_existing'], $this->options['replace_interests'], $this->options['send_welcome'] );
		}

		// if result failed, show error message
		if( ! $result ) {

			// log error
			if( $api->get_error_code() === 214 ) {
				$this->get_log()->warning( sprintf( "%s > %s is already subscribed to the selected list(s)", $this->name, mc4wp_obfuscate_string( $email ) ) );
			} else {
				$this->get_log()->error( sprintf( '%s > MailChimp API Error: %s', $this->name, $api->get_error_message() ) );
			}

			// bail
			return false;
		}

		$this->get_log()->info( sprintf( '%s > Successfully subscribed %s', $this->name, $email ) );

		/**
		 * Runs right after someone is subscribed using an integration
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Integration $integration
		 * @param string $email
		 * @param array $merge_vars
		 * @param int $related_object_id
		 */
		do_action( 'mc4wp_integration_subscribed', $integration, $email, $merge_vars, $related_object_id );

		return $result;
	}

	/**
	 * Are the required dependencies for this integration installed?
	 *
	 * @return bool
	 */
	public function is_installed() {
		return false;
	}

	/**
	 * Which UI elements should we show on the settings page for this integration?
	 *
	 * @return array
	 */
	public function get_ui_elements() {
		return array_keys( $this->options );
	}

	/**
	 * Does integration have the given UI element?
	 *
	 * @param $element
	 * @return bool
	 */
	public function has_ui_element( $element ) {
		$elements = $this->get_ui_elements();
		return in_array( $element, $elements );
	}

	/**
	 * Return a string to the admin settings page for this object (if any)
	 *
	 * @param int $object_id
	 * @return string
	 */
	public function get_object_link( $object_id ) {
		return '';
	}

	/**
	 * Get the data for this integration request
	 *
	 * By default, this will return a combination of all $_GET and $_POST parameters.
	 * Override this method if you need data from somewhere else.
	 *
	 * This data should contain the value of the checkbox (required)
	 * and the lists to which should be subscribed (optional)
	 *
	 * @see MC4WP_Integration::$checkbox_name
	 * @see MC4WP_Integration::get_lists
	 * @see MC4WP_Integration::checkbox_was_checked
	 *
	 * @return array
	 */
	public function get_data() {
		$request = mc4wp('request');
		$data = $request->params->all();
		return $data;
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	protected function get_log() {
		return mc4wp('log');
	}

}