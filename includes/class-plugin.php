<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite {
	private static $instance;

	public static function instance() {
		return self::$instance;
	}

	public static function init() {
		if(self::$instance) {
			throw new Exception("ALready initialized.");
		} else {
			self::$instance = new self;
		}
	}

	private function __construct() {
		$this->backwards_compatibility();

		// checkbox
		require_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-checkbox.php';
		MC4WP_Lite_Checkbox::init();

		// form
		require_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-form.php';
		MC4WP_Lite_Form::init();

		// widget
		add_action( 'widgets_init', array($this, 'register_widget') );

		if (!is_admin()) {
			// frontend only
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/template-functions.php';

			// load css
			add_action( 'wp_enqueue_scripts', array($this, 'load_stylesheets'), 90);
			add_action( 'login_enqueue_scripts',  array($this, 'load_stylesheets') );
		}
	}

	private function backwards_compatibility() {
		$options = get_option( 'mc4wp_lite' );

		// transfer widget to new id?
		if(get_option('mc4wp_transfered_old_widgets', false) == false) {
			$sidebars_widgets = get_option('sidebars_widgets');
			
			if($sidebars_widgets && is_array($sidebars_widgets)) {
				foreach($sidebars_widgets as $key => $widgets) 
				{
					if(!is_array($widgets)) { continue; }
					foreach($widgets as $subkey => $widget_name) {

						if(substr($widget_name, 0, 17) == 'mc4wp_lite_widget') {

							$new_widget_name = str_replace('mc4wp_lite_widget', 'mc4wp_widget', $widget_name);
							// active widget found, just change name?
							$sidebars_widgets[$key][$subkey] = $new_widget_name;
							update_option('sidebars_widgets', $sidebars_widgets);
							update_option('widget_mc4wp_widget', get_option('widget_mc4wp_lite_widget') );
							break;
						}
					}
				}
			}

			update_option('mc4wp_transfered_old_widgets', true);
		}
		
		
		// transfer old options to new options format
		if (isset( $options['mailchimp_api_key'] )) {  

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
		} // end transfer options
	}

	public function register_widget()
	{
		include_once MC4WP_LITE_PLUGIN_DIR . 'includes/class-widget.php';
		register_widget( 'MC4WP_Lite_Widget' );
	}

	public function load_stylesheets() 
	{
		$stylesheets = apply_filters('mc4wp_stylesheets', array());

		if(!empty($stylesheets)) {
			$stylesheet_url = add_query_arg($stylesheets, MC4WP_LITE_PLUGIN_URL . 'assets/css/css.php' );
			wp_enqueue_style( 'mailchimp-for-wp', $stylesheet_url, array(), MC4WP_LITE_VERSION);
		}
	}

}
