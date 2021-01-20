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
		$this->slug    = $slug;
		$this->options = $this->parse_options( $options );

		// if checkbox name is not set, set a good custom value
		if ( $this->checkbox_name === '' ) {
			$this->checkbox_name = '_mc4wp_subscribe_' . $this->slug;
		}
	}

	/**
	 * Return array of default options
	 *
	 * @return array
	 */
	protected function get_default_options() {
		return array(
			'css'               => 0,
			'double_optin'      => 1,
			'enabled'           => 0,
			'implicit'          => 0,
			'label'             => __( 'Sign me up for the newsletter!', 'mailchimp-for-wp' ),
			'lists'             => array(),
			'precheck'          => 0,
			'replace_interests' => 0,
			'update_existing'   => 0,
			'wrap_p'            => 1,
		);
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 */
	protected function parse_options( array $options ) {
		$slug = $this->slug;

		$default_options = $this->get_default_options();
		$options         = array_merge( $default_options, $options );

		/**
		 * @deprecated Use mc4wp_integration_{$slug}_options instead
		 */
		$options = (array) apply_filters( 'mc4wp_' . $slug . '_integration_options', $options );

		/**
		 * Filters options for a specific integration
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the slug of the ingration.
		 *
		 * @param array $integration_options
		 */
		return (array) apply_filters( 'mc4wp_integration_' . $slug . '_options', $options );
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
		if ( $this->options['css'] && ! $this->options['implicit'] ) {
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
		$css    = file_get_contents( MC4WP_PLUGIN_DIR . 'assets/css/checkbox-reset' . $suffix . '.css' );

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
		$label       = $this->options['label'];

		/**
		 * Filters the checkbox label
		 *
		 * @since 3.0
		 *
		 * @param string $label
		 * @param MC4WP_Integration $integration
		 * @ignore
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
		return isset( $data[ $this->checkbox_name ] ) && (int) $data[ $this->checkbox_name ] === 1;
	}

	/**
	 * Get a string of attributes for the checkbox element.
	 *
	 * @return string
	 */
	protected function get_checkbox_attributes() {
		$integration = $this;
		$slug        = $this->slug;

		$attributes = array();

		if ( $this->options['precheck'] ) {
			$attributes['checked'] = 'checked';
		}

		/**
		 * Filters the attributes array.
		 *
		 * @param array $attributes
		 * @param MC4WP_Integration $integration
		 * @ignore
		 */
		$attributes = (array) apply_filters( 'mc4wp_integration_checkbox_attributes', $attributes, $integration );

		/**
		 * Filters the attributes array.
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the slug for this integration.
		 *
		 * @param array $attributes
		 * @param MC4WP_Integration $integration
		 * @ignore
		 */
		$attributes = (array) apply_filters( 'mc4wp_integration_' . $slug . '_checkbox_attributes', $attributes, $integration );

		$string = '';
		foreach ( $attributes as $key => $value ) {
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
	 * @param array $html_attrs
	 * @return string
	 */
	public function get_checkbox_html( array $html_attrs = array() ) {
		$show_checkbox    = empty( $this->options['implicit'] );
		$integration_slug = $this->slug;

		/**
		 * Filters whether to show the sign-up checkbox for this integration.
		 *
		 * @param bool $show_checkbox
		 * @param string $integration_slug
		 */
		$show_checkbox = (bool) apply_filters( 'mc4wp_integration_show_checkbox', $show_checkbox, $integration_slug );

		if ( ! $show_checkbox ) {
			return '';
		}

		ob_start();

		echo sprintf( '<!-- Mailchimp for WordPress v%s - https://www.mc4wp.com/ -->', MC4WP_VERSION );

		/** @ignore */
		do_action( 'mc4wp_integration_before_checkbox_wrapper', $this );

		/** @ignore */
		do_action( 'mc4wp_integration_' . $this->slug . '_before_checkbox_wrapper', $this );

		$wrapper_tag = $this->options['wrap_p'] ? 'p' : 'span';

		$html_attrs          = array_merge(
			array(
				'class' => '',
			),
			$html_attrs
		);
		$html_attrs['class'] = $html_attrs['class'] . sprintf( ' mc4wp-checkbox mc4wp-checkbox-%s', $this->slug );

		$html_attr_str = '';
		foreach ( $html_attrs as $key => $value ) {
			$html_attr_str .= sprintf( '%s="%s" ', $key, esc_attr( $value ) );
		}

		// Hidden field to make sure "0" is sent to server
		echo sprintf( '<input type="hidden" name="%s" value="0" />', esc_attr( $this->checkbox_name ) );
		echo sprintf( '<%s %s>', $wrapper_tag, $html_attr_str );
		echo '<label>';
		echo sprintf( '<input type="checkbox" name="%s" value="1" %s />', esc_attr( $this->checkbox_name ), $this->get_checkbox_attributes() );
		echo sprintf( '<span>%s</span>', $this->get_label_text() );
		echo '</label>';
		echo sprintf( '</%s>', $wrapper_tag );

		/** @ignore */
		do_action( 'mc4wp_integration_after_checkbox_wrapper', $this );

		/** @ignore */
		do_action( 'mc4wp_integration_' . $this->slug . '_after_checkbox_wrapper', $this );
		echo '<!-- / Mailchimp for WordPress -->';

		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Get the selected Mailchimp lists
	 *
	 * @return array Array of List ID's
	 */
	public function get_lists() {
		$data        = $this->get_data();
		$integration = $this;
		$slug        = $this->slug;

		// get checkbox lists options
		$lists = $this->options['lists'];

		// get lists from request, if set.
		if ( ! empty( $data['_mc4wp_lists'] ) ) {
			$lists = $data['_mc4wp_lists'];

			// ensure lists is an array
			if ( ! is_array( $lists ) ) {
				$lists = explode( ',', $lists );
				$lists = array_map( 'trim', $lists );
			}
		}

		/**
		 * Allow plugins to filter final lists value. This filter is documented elsewhere.
		 *
		 * @since 2.0
		 * @see MC4WP_Form::get_lists
		 * @ignore
		 */
		$lists = (array) apply_filters( 'mc4wp_lists', $lists );

		/**
		 * Filters the Mailchimp lists this integration should subscribe to
		 *
		 * @since 3.0
		 *
		 * @param array $lists
		 * @param MC4WP_Integration $integration
		 */
		$lists = (array) apply_filters( 'mc4wp_integration_lists', $lists, $integration );

		/**
		 * Filters the Mailchimp lists a specific integration should subscribe to
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
	 * @param array $data
	 * @param int $related_object_id
	 *
	 * @return boolean
	 */
	protected function subscribe( array $data, $related_object_id = 0 ) {
		$integration = $this;
		$slug        = $this->slug;
		$mailchimp   = new MC4WP_MailChimp();
		$log         = $this->get_log();
		$list_ids    = $this->get_lists();

		/** @var MC4WP_MailChimp_Subscriber $subscriber */
		$subscriber = null;
		$result     = false;

		// validate lists
		if ( empty( $list_ids ) ) {
			$log->warning( sprintf( '%s > No Mailchimp lists were selected', $this->name ) );
			return false;
		}

		/**
		 * Filters data for integration requests.
		 *
		 * @param array $data
		 */
		$data = apply_filters( 'mc4wp_integration_data', $data );

		/**
		 * Filters data for a specific integration request.
		 *
		 * The dynamic portion of the hook, `$slug`, refers to the integration slug.
		 *
		 * @param array $data
		 * @param int $related_object_id
		 */
		$data = apply_filters( "mc4wp_integration_{$slug}_data", $data, $related_object_id );

		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = apply_filters( 'mc4wp_merge_vars', $data );

		/**
		 * @deprecated 4.0
		 * @ignore
		 */
		$data = apply_filters( 'mc4wp_integration_merge_vars', $data, $integration );

		/**
		 * @deprecated 4.0
		 * @ignore
		 */
		$data = apply_filters( "mc4wp_integration_{$slug}_merge_vars", $data, $integration );

		$email_type = mc4wp_get_email_type();

		$mapper = new MC4WP_List_Data_Mapper( $data, $list_ids );

		/** @var MC4WP_MailChimp_Subscriber[] $map */
		$map = $mapper->map();

		foreach ( $map as $list_id => $subscriber ) {
			$subscriber->status     = $this->options['double_optin'] ? 'pending' : 'subscribed';
			$subscriber->email_type = $email_type;
			$subscriber->ip_signup  = mc4wp_get_request_ip_address();

			/** @ignore (documented elsewhere) */
			$subscriber = apply_filters( 'mc4wp_subscriber_data', $subscriber );
			if ( ! $subscriber instanceof MC4WP_MailChimp_Subscriber ) {
				continue;
			}

			/**
			 * Filters subscriber data before it is sent to Mailchimp. Only fires for integration requests.
			 *
			 * @param MC4WP_MailChimp_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'mc4wp_integration_subscriber_data', $subscriber );
			if ( ! $subscriber instanceof MC4WP_MailChimp_Subscriber ) {
				continue;
			}

			/**
			 * Filters subscriber data before it is sent to Mailchimp. Only fires for integration requests.
			 *
			 * The dynamic portion of the hook, `$slug`, refers to the integration slug.
			 *
			 * @param MC4WP_MailChimp_Subscriber $subscriber
			 * @param int $related_object_id
			 */
			$subscriber = apply_filters( "mc4wp_integration_{$slug}_subscriber_data", $subscriber, $related_object_id );
			if ( ! $subscriber instanceof MC4WP_MailChimp_Subscriber ) {
				continue;
			}

			$result = $mailchimp->list_subscribe( $list_id, $subscriber->email_address, $subscriber->to_array(), $this->options['update_existing'], $this->options['replace_interests'] );
		}

		// if result failed, show error message
		if ( ! $result ) {

			// log error
			if ( (int) $mailchimp->get_error_code() === 214 ) {
				$log->warning( sprintf( '%s > %s is already subscribed to the selected list(s)', $this->name, $subscriber->email_address ) );
			} else {
				$log->error( sprintf( '%s > Mailchimp API Error: %s', $this->name, $mailchimp->get_error_message() ) );
			}

			// bail
			return false;
		}

		$log->info( sprintf( '%s > Successfully subscribed %s', $this->name, $subscriber->email_address ) );

		/**
		 * Runs right after someone is subscribed using an integration
		 *
		 * @since 3.0
		 *
		 * @param MC4WP_Integration $integration
		 * @param string $email_address
		 * @param array $merge_vars
		 * @param MC4WP_MailChimp_Subscriber[] $subscriber_data
		 * @param int $related_object_id
		 */
		do_action( 'mc4wp_integration_subscribed', $integration, $subscriber->email_address, $subscriber->merge_fields, $map, $related_object_id );

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
	 * @param string $element
	 * @return bool
	 */
	public function has_ui_element( $element ) {
		$elements = $this->get_ui_elements();
		return in_array( $element, $elements, true );
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
		$data = array_merge( (array) $_GET, (array) $_POST );
		return $data;
	}

	/**
	 * @return MC4WP_Debug_Log
	 */
	protected function get_log() {
		return mc4wp( 'log' );
	}

	/**
	 * @return MC4WP_API_V3
	 */
	protected function get_api() {
		return mc4wp( 'api' );
	}
}
