<?php

/**
 * Class MC4WP_Integration
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
		$this->options = $options;
		$this->request_data = $_REQUEST;

		// if checkbox name is not set, set a good custom value
		if( empty( $this->checkbox_name ) ) {
			$this->checkbox_name = '_mc4wp_subscribe' . '_' . $this->slug;
		}
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
	 * Outputs a checkbox
	 */
	public function output_checkbox() {
		echo $this->get_checkbox();
	}

	/**
	 * @param string $label
	 * @param bool $precheck
	 * @return string
	 * @todo move to actions instead of filters
	 */
	public function get_checkbox( $label = '', $precheck = null ) {

		if( empty( $label ) ) {
			$label = $this->get_label_text();
		}

		if( is_null( $precheck ) ) {
			$precheck = $this->options['precheck'];
		}

		// before checkbox HTML (comment, ...)
		$before = '<!-- MailChimp for WordPress v'. MC4WP_VERSION .' - https://mc4wp.com/ -->';
		$before .= apply_filters( 'mc4wp_before_checkbox', '', $this->slug );

		// checkbox
		$content = '<p class="mc4wp-checkbox mc4wp-checkbox-' . $this->slug .'">';
		$content .= '<label>';
		$content .= '<input type="checkbox" name="'. esc_attr( $this->checkbox_name ) .'" value="1" '. checked( $precheck, true, false ) . '/> ';
		$content .= $label;
		$content .= '</label>';
		$content .= '</p>';

		// after checkbox HTML (..., honeypot, closing comment)
		$after = apply_filters( 'mc4wp_after_checkbox', '', $this->slug );
		$after .= '<div style="display: none;"><input type="text" name="_mc4wp_required_but_not_really" value="" tabindex="-1" autocomplete="off" /></div>';
		$after .= '<!-- / MailChimp for WordPress -->';

		return $before . $content . $after;
	}

	/**
	 * @return array
	 */
	protected function get_lists() {

		// get checkbox lists options
		$lists = $this->options['lists'];

		// get lists from request, if set.
		if( ! empty( $_POST['_mc4wp_lists'] ) ) {

			$lists = $_POST['_mc4wp_lists'];

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

		if( empty( $lists) ) {

			// show helpful error message to admins, but only if not using ajax
			if( $this->show_error_messages() ) {
				wp_die(
					'<h3>' . __( 'MailChimp for WordPress - Error', 'mailchimp-for-wp' ) . '</h3>' .
					'<p>' . sprintf( __( 'Please select a list to subscribe to in the <a href="%s">checkbox settings</a>.', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp-checkbox-settings' ) ) . '</p>' .
					'<p style="font-style:italic; font-size:12px;">' . __( 'This message is only visible to administrators for debugging purposes.', 'mailchimp-for-wp' ) . '</p>',
					__( 'MailChimp for WordPress - Error', 'mailchimp-for-wp' ),
					array( 'back_link' => true )
				);
			}

			return 'no_lists_selected';
		}

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

			// log error
			error_log( sprintf( 'MailChimp for WordPres (%s): %s', date( 'Y-m-d H:i:s' ), $this->slug, $api->get_error_message() ) );

			if( $this->show_error_messages() ) {
				wp_die( '<h3>' . __( 'MailChimp for WordPress - Error', 'mailchimp-for-wp' ) . '</h3>' .
				        '<p>' . __( 'The MailChimp server returned the following error message as a response to our sign-up request:', 'mailchimp-for-wp' ) . '</p>' .
				        '<pre>' . $api->get_error_message() . '</pre>' .
				        '<p>' . __( 'This is the data that was sent to MailChimp:', 'mailchimp-for-wp' ) . '</p>' .
				        '<strong>' . __( 'Email address:', 'mailchimp-for-wp' ) . '</strong>' .
				        '<pre>' . esc_html( $email ) . '</pre>' .
				        '<strong>' . __( 'Merge variables:', 'mailchimp-for-wp' ) . '</strong>' .
				        '<pre>' . esc_html( print_r( $merge_vars, true ) ) . '</pre>' .
				        '<p style="font-style:italic; font-size:12px;">' . __( 'This message is only visible to administrators for debugging purposes.', 'mailchimp-for-wp' ) . '</p>',
					__( 'MailChimp for WordPress - Error', 'mailchimp-for-wp' ), array( 'back_link' => true ) );
			}
		}

		return $result;
	}

	/**
	 * Should we show error messages?
	 * - Not for AJAX requests
	 * - Not for non-admins
	 * @todo Not for CF7 requests (which uses a different AJAX mechanism)
	 *
	 * @return bool
	 */
	protected function show_error_messages() {
		return ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && current_user_can( 'manage_options' );
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