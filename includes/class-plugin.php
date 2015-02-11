<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
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
	* @var MC4WP_API
	*/
	private $api;

	/**
	 * @var MC4WP_Lite The one and only true plugin instance
	 */
	private static $instance;

	/**
	 * @return MC4WP_Lite
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Instantiates the plugin
	 *
	 * @return bool
	 */
	public static function init() {

		if( self::$instance instanceof MC4WP_Lite ) {
			return false;
		}

		self::$instance = new MC4WP_Lite;
		return true;
	}


	/**
	* Constructor
	*/
	private function __construct() {

		spl_autoload_register( array( $this, 'autoload') );

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
	public function autoload( $class_name ) {

		static $classes = null;

		if( $classes === null ) {

	        $classes = array(
		        'MC4WP_API'                             => 'class-api.php',
		        'MC4WP_Lite_Checkbox_Manager'                => 'class-checkbox-manager.php',
		        'MC4WP_Lite_Form_Manager'                    => 'class-form-manager.php',
		        'MC4WP_Lite_Form_Request'                    => 'class-form-request.php',
		        'MC4WP_Lite_Widget'                          => 'class-widget.php',
		        'MC4WP_MailChimp'                            => 'class-mailchimp.php',

		        // integrations
		        'MC4WP_Integration'                     => 'integrations/class-integration.php',
		        'MC4WP_bbPress_Integration'             => 'integrations/class-bbpress.php',
		        'MC4WP_BuddyPress_Integration'          => 'integrations/class-buddypress.php',
		        'MC4WP_CF7_Integration'                 => 'integrations/class-cf7.php',
		        'MC4WP_Events_Manager_Integration'      => 'integrations/class-events-manager.php',
		        'MC4WP_Comment_Form_Integration'        => 'integrations/class-comment-form.php',
		        'MC4WP_General_Integration'             => 'integrations/class-general.php',
		        'MC4WP_MultiSite_Integration'           => 'integrations/class-multisite.php',
		        'MC4WP_Registration_Form_Integration'   => 'integrations/class-registration-form.php',
		        'MC4WP_WooCommerce_Integration'         => 'integrations/class-woocommerce.php',
		        'MC4WP_EDD_Integration'                 => 'integrations/class-edd.php',
	        );

		}

		if( isset( $classes[$class_name] ) ) {
			require_once MC4WP_LITE_PLUGIN_DIR . 'includes/' . $classes[$class_name];
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
	* @return MC4WP_API
	*/
	public function get_api() {

		if( $this->api === null ) {
			$opts = mc4wp_get_options();
			$this->api = new MC4WP_API( $opts['general']['api_key'] );
		}

		return $this->api;
	}

	public function register_widget() {
		register_widget( 'MC4WP_Lite_Widget' );
	}

}
