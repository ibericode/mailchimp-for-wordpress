<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite {

	/**
	* @var MC4WP_Lite_Form_Manager
	*/
	private $form_manager;

	/**
	* @var MC4WP_Lite_Checkbox_Manager
	*/
	private $checkbox_manager;

	/**
	* @var MC4WP_Lite_API
	*/
	private $api = null;

	/**
	* @var string Code version number
	*/
	private $code_version = "1.0";

	/**
	* Constructor
	*/
	public function __construct() {

        spl_autoload_register( array( $this, 'autoload') );

		// Check whether to run the upgrade routine
		$db_code_version = get_option( 'mc4wp_code_version', 0 );
		if( version_compare( $this->code_version, $db_code_version, "<" ) ) {
			$this->upgrade();
		}

		// checkbox
		$this->checkbox_manager = new MC4WP_Lite_Checkbox_Manager();

		// form
		$this->form_manager = new MC4WP_Lite_Form_Manager();

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

    /**
     * @return bool
     */
    public function autoload( $class ) {

        static $classes = null;

        if( $classes === null ) {

            $include_path = MC4WP_LITE_PLUGIN_DIR . 'includes/';

            $classes = array(
                'mc4wp_lite_api' => $include_path . 'class-api.php',
                'mc4wp_lite_checkbox_manager' => $include_path . 'class-checkbox-manager.php',
                'mc4wp_lite_form_manager' => $include_path . 'class-form-manager.php',
                'mc4wp_lite_widget' => $include_path . 'class-widget.php',
                'mc4wp_lite_form_request' => $include_path . 'class-form-request.php',
	            'mc4wp_mailchimp' => $include_path . 'class-mailchimp.php',

                // integrations
                'mc4wp_integration' => $include_path . 'integrations/class-integration.php',
                'mc4wp_bbpress_integration' => $include_path . 'integrations/class-bbpress.php',
                'mc4wp_buddypress_integration' => $include_path . 'integrations/class-buddypress.php',
                'mc4wp_cf7_integration' => $include_path . 'integrations/class-cf7.php',
                'mc4wp_comment_form_integration' => $include_path . 'integrations/class-comment-form.php',
                'mc4wp_general_integration' => $include_path . 'integrations/class-general.php',
                'mc4wp_multisite_integration' => $include_path . 'integrations/class-multisite.php',
                'mc4wp_registration_form_integration' => $include_path . 'integrations/class-registration-form.php',
            );
        }

        $class_name = strtolower( $class );

        if( isset( $classes[$class_name] ) ) {
            require_once $classes[$class_name];
            return true;
        }

        return false;


    }

	/**
	* @return MC4WP_Lite_Checkbox
	*/
	public function get_checkbox_manager() {
		return $this->checkbox_manager;
	}

	/**
	* @return MC4WP_Lite_Form
	*/
	public function get_form_manager() {
		return $this->form_manager;
	}

	/**
	* @return MC4WP_Lite_API
	*/
	public function get_api() {

		if( $this->api === null ) {
			$opts = mc4wp_get_options();
			$this->api = new MC4WP_Lite_API( $opts['general']['api_key'] );
		}
		
		return $this->api;
	}

	private function upgrade() {
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

		// update code version
		update_option( 'mc4wp_code_version', $this->code_version );
	}

	public function register_widget()
	{
		register_widget( 'MC4WP_Lite_Widget' );
	}

}
