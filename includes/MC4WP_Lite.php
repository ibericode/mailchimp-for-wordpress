<?php

class MC4WP_Lite {
	private $options = array();
	private static $instance, $checkbox = null, $form = null, $api = null, $admin = null;

	public static function instance() {
		return self::$instance;
	}

	public static function checkbox() {
		if ( !self::$checkbox ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/MC4WP_Lite_Checkbox.php';
			self::$checkbox = new MC4WP_Lite_Checkbox();
		}
		return self::$checkbox;
	}

	public static function form() {
		if ( !self::$form ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/MC4WP_Lite_Form.php';
			self::$form = new MC4WP_Lite_Form();
		}
		return self::$form;
	}

	public static function api() {
		if ( !self::$api ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/MC4WP_Lite_API.php';
			$opts = self::instance()->get_options();
			self::$api = new MC4WP_Lite_API( $opts['general']['api_key'] );
		}
		return self::$api;
	}

	public static function admin() {
		if ( !self::$admin ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/MC4WP_Lite_Admin.php';
			self::$admin = new MC4WP_Lite_Admin();
		}
		return self::$admin;
	}

	public function __construct() {
		self::$instance = $this;

		$this->backwards_compatibility();
		$opts = $this->get_options();

		// checkbox
		self::checkbox();

		// form
		self::form();

		// widget
		add_action( 'widgets_init', array($this, 'register_widget') );

		if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {
			if ( is_admin() ) {
				// backend only
				self::admin();
			} else {
				// frontend only
				include_once MC4WP_LITE_PLUGIN_DIR . 'includes/template-functions.php';

				// load css
				add_action( 'wp_enqueue_scripts', array($this, 'load_stylesheets'), 90);
				add_action( 'login_enqueue_scripts',  array($this, 'load_stylesheets') );
			}
		}
	}

	private function get_default_options() {
		return array(
			'general' => array(
				'api_key' => ''
			),
			'checkbox' => array(
				'label' => 'Sign me up for the newsletter!',
				'precheck' => 1,
				'css' => 1,
				'show_at_comment_form' => 0,
				'show_at_registration_form' => 0,
				'show_at_multisite_form' => 0,
				'show_at_buddypress_form' => 0,
				'show_at_bbpress_forms' => 0,
				'show_at_other_forms' => 0,
				'lists' => array(),
				'double_optin' => 1
			),
			'form' => array(
				'css' => 1,
				'markup' => "<p>\n\t<label for=\"mc4wp_email\">Email address: </label>\n\t<input type=\"email\" id=\"mc4wp_email\" name=\"EMAIL\" required placeholder=\"Your email address\" />\n</p>\n\n<p>\n\t<input type=\"submit\" value=\"Sign up\" />\n</p>",
				'text_success' => 'Thank you, your sign-up request was successful! Please check your e-mail inbox.',
				'text_error' => 'Oops. Something went wrong. Please try again later.',
				'text_invalid_email' => 'Please provide a valid email address.',
				'text_already_subscribed' => "Given email address is already subscribed, thank you!",
				'redirect' => '',
				'lists' => array(),
				'double_optin' => 1,
				'hide_after_success' => 0
			)
		);
	}

	public function get_options() {
		if ( empty( $this->options ) ) {

			$defaults = $this->get_default_options();
			$db_keys_option_keys = array(
				'mc4wp_lite' => 'general',
				'mc4wp_lite_checkbox' => 'checkbox',
				'mc4wp_lite_form' => 'form'
			);

			foreach ( $db_keys_option_keys as $db_key => $option_key ) {
				$option = get_option( $db_key );

				// add option to database to prevent query on every pageload
				if ( $option == false ) { add_option( $db_key, $defaults[$option_key] ); }

				$this->options[$option_key] = array_merge( $defaults[$option_key], (array) $option );
			}
		}

		return $this->options;
	}

	private function backwards_compatibility() {
		$options = get_option( 'mc4wp_lite' );

		if ( !isset( $options['mailchimp_api_key'] ) ) { return; }

		// transfer old options to new option system
		$new_options = array(
			'general' => array(),
			'checkbox' => array(),
			'form' => array()
		);

		$new_options['general']['api_key'] = $options['mailchimp_api_key'];

		foreach ( $options as $key => $value ) {
			$_pos = strpos( $key, '_' );

			$first_key = substr( $key, 0, $_pos );
			$second_key = substr( $key, $_pos + 1 );

			if ( isset( $new_options[$first_key] ) ) {

				// change option name
				if ( $second_key == 'show_at_bp_form' ) {
					$second_key = 'show_at_buddypress_form';
				}

				// change option name
				if ( $second_key == 'show_at_ms_form' ) {
					$second_key = 'show_at_multisite_form';
				}

				// set value into new option name
				$new_options[$first_key][$second_key] = $value;
			}

		}

		update_option( 'mc4wp_lite', $new_options['general'] );
		update_option( 'mc4wp_lite_checkbox', $new_options['checkbox'] );
		update_option( 'mc4wp_lite_form', $new_options['form'] );

	}

	public function register_widget()
	{
		include_once MC4WP_LITE_PLUGIN_DIR . 'includes/MC4WP_Lite_Widget.php';
		register_widget( 'MC4WP_Lite_Widget' );
	}

	public function load_stylesheets() 
	{
		$stylesheets = apply_filters('mc4wp_stylesheets', array());

		if(!empty($stylesheets)) {
			$stylesheet_url = add_query_arg($stylesheets, plugins_url('mailchimp-for-wp/assets/css/css.php'));
			wp_enqueue_style( 'mailchimp-for-wp', $stylesheet_url, array(), MC4WP_LITE_VERSION);
		}
	}

}
