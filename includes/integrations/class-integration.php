<?php

/**
 * Class MC4WP_Integration
 *
 * @api
 * @since 3.0
 */
abstract class MC4WP_Integration {

	/**
	 * @var
	 */
	public $name = '';

	/**
	 * @var
	 */
	public $description = '';

	/**
	 * @var
	 */
	public $slug = '';

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	protected $checkbox_name = '';

	/**
	 * @var
	 */
	protected $request_data;

	/**
	 * Constructor
	 *
	 * @param string $slug
	 * @param array $options
	 */
	public function __construct( $slug, array $options ) {
		$this->slug = $slug;
		$this->options = $this->parse_options( $options );
		$this->request_data = $_REQUEST;

		// if checkbox name is not set, set a good custom value
		if( empty( $this->checkbox_name ) ) {
			$this->checkbox_name = '_mc4wp_subscribe' . '_' . $this->slug;
		}
	}

	/**
	 * @return array
	 */
	protected function get_default_options() {
		$defaults = require MC4WP_PLUGIN_DIR . 'config/default-integration-options.php';
		$integration_options = array_merge( $defaults, $this->options );
		return (array) apply_filters( 'mc4wp_' . $this->slug . '_integration_options', $integration_options );
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	protected function parse_options( array $options ) {
		return array_merge( $this->get_default_options(), $options );
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
		if( $this->options['css'] ) {
			add_action( 'wp_head', array( $this, 'print_css_reset' ) );
		}
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
	 * Was the honeypot filled?
	 *
	 * @todo current way of checking means honeypot field can be omitted, needs improvement.
	 * @return bool
	 */
	protected function is_honeypot_filled() {
		return ! empty( $this->request_data[ '_mc4wp_required_but_not_really' ] );
	}

	/**
	 * Get the text for the label element
	 *
	 * @return string
	 */
	public function get_label_text() {

		// Get general label text
		$label = $this->options['label'];

		// replace label variables
		// @todo move this to filter?
		$label = MC4WP_Tools::replace_variables( $label, array(), array_values( $this->options['lists'] ) );

		return $label;
	}

	/**
	 * Was the integration checkbox checked?
	 *
	 * @return bool
	 */
	public function checkbox_was_checked() {
		return ( isset( $this->request_data[ $this->checkbox_name ] ) && $this->request_data[ $this->checkbox_name ] == 1 );
	}

	/**
	 * @return string
	 */
	protected function get_checkbox_attributes() {

		$attributes = array();

		if( $this->options['precheck'] ) {
			$attributes['checked'] = 'checked';
		}

		$attributes = (array) apply_filters( 'mc4wp_integration_checkbox_attributes', $attributes, $this );
		$attributes = (array) apply_filters( 'mc4wp_integration_' . $this->slug . '_checkbox_attributes', $attributes, $this );

		return join( ' ', $attributes );
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

		<div style="display: none;"><input type="text" name="_mc4wp_required_but_not_really" value="" tabindex="-1" autocomplete="off" /></div>

		<?php do_action( 'mc4wp_integration_after_checkbox_wrapper', $this ); ?>
		<?php do_action( 'mc4wp_integration_'. $this->slug .'_after_checkbox_wrapper', $this ); ?>

		<!-- / MailChimp for WordPress -->
		<?php
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * @return array
	 */
	protected function get_lists() {

		// get checkbox lists options
		$lists = $this->options['lists'];

		// get lists from request, if set.
		if( ! empty( $this->request_data['_mc4wp_lists'] ) ) {

			$lists = $this->request_data['_mc4wp_lists'];
			if( ! is_array( $lists ) ) {
				$lists = explode( ',', $lists );
			}

			$lists = array_map( 'sanitize_text_field', $lists );
		}

		// allow plugins to filter final lists value
		$lists = (array) apply_filters( 'mc4wp_lists', $lists );

		return $lists;
	}

	/**
	 * Makes a subscription request
	 *
	 * @param string $email
	 * @param array $merge_vars
	 * @param int $related_object_id
	 * @todo move certain checks to `validate` logic
	 * @return string|boolean
	 */
	protected function subscribe( $email, array $merge_vars = array(), $related_object_id = 0 ) {

		$api = mc4wp_get_api();
		$lists = $this->get_lists();

		// @todo decouple
		$merge_vars = MC4WP_Tools::guess_merge_vars( $merge_vars );

		// set ip address
		if( ! isset( $merge_vars['OPTIN_IP'] ) ) {
			$merge_vars['OPTIN_IP'] = MC4WP_Tools::get_client_ip();
		}

		$result = false;

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects array
		 * @param array $merge_vars
		 * @param string $slug
		 *
		 * Use this to filter the final merge vars before the request is sent to MailChimp
		 */
		$merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, $this->slug );

		/**
		 * @filter `mc4wp_integration_merge_vars`
		 * @expects array
		 * @param array $merge_vars
		 * @param string $slug
		 *
		 * Use this to filter the final merge vars before the request is sent to MailChimp
		 */
		$merge_vars = apply_filters( 'mc4wp_integration_merge_vars', $merge_vars, $this->slug );

		/**
		 * @filter `mc4wp_integration_merge_vars`
		 * @expects array
		 * @param array $merge_vars
		 * @param string $slug
		 *
		 * Use this to filter the final merge vars before the request is sent to MailChimp
		 */
		$merge_vars = apply_filters( 'mc4wp_integration_' . $this->slug . '_merge_vars', $merge_vars );

		/**
		 * @filter `mc4wp_merge_vars`
		 * @expects string
		 * @param string $email_type
		 *
		 * Use this to change the email type this users should receive
		 */
		$email_type = apply_filters( 'mc4wp_email_type', 'html' );

		/**
		 * @action `mc4wp_before_subscribe`
		 * @param string $email
		 * @param array $merge_vars
		 *
		 * Runs before the request is sent to MailChimp
		 */
		do_action( 'mc4wp_before_subscribe', $email, $merge_vars );

		foreach( $lists as $list_id ) {
			$result = $api->subscribe( $list_id, $email, $merge_vars, $email_type, $this->options['double_optin'], $this->options['update_existing'], true, $this->options['send_welcome'] );
			do_action( 'mc4wp_subscribe', $email, $list_id, $merge_vars, $result, 'checkbox', $this->slug, $related_object_id );
		}

		/**
		 * @action `mc4wp_after_subscribe`
		 * @param string $email
		 * @param array $merge_vars
		 * @param boolean $result
		 *
		 * Runs after the request is sent to MailChimp
		 */
		do_action( 'mc4wp_after_subscribe', $email, $merge_vars, $result );

		// if result failed, show error message (only to admins for non-AJAX)
		if ( $result !== true && $api->has_error() ) {
			error_log( sprintf( 'MailChimp for WordPres (%s): %s', $this->slug, $api->get_error_message() ) );
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return false;
	}

	/**
	 * @return array
	 */
	public function get_ui_elements() {
		return array_keys( $this->options );
	}

	/**
	 * @param $element
	 *
	 * @return bool
	 */
	public function has_ui_element( $element ) {
		$elements = $this->get_ui_elements();
		return in_array( $element, $elements );
	}
}