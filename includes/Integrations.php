<?php

class MC4WP_Integrations {

	protected $integrations = array();

	protected $registered_integrations = array(
		'comment_form' => 'Comment_Form',
		'registration_form' => 'Registration_Form',
		'buddypress'  => 'BuddyPress',
		'bbpress'  => 'bbPress',
		'woocommerce'  => 'WooCommerce',
		'easy_digital_downloads'  => 'EDD',
		'contact_form_7'  => 'CF7',
		'events_manager'  => 'Events_Manager',
		'multisite' => 'MultiSite',
		'custom'  => 'General',

	);

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @param array $options
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * @param $name
	 *
	 * @return null
	 */
	public function __get( $name ) {

		if( isset( $this->integrations[ $name ] ) ) {
			return $this->integrations[ $name ];
		}

		if( isset( $this->registered_integrations[ $name ] ) ) {
			$classname = 'MC4WP_' . $this->registered_integrations[ $name ] .'_Integration';
			$this->integrations[ $name ] = new $classname;
			return $this->integrations[ $name ];
		}

		return null;
	}

	public function load() {

		// Load WP Comment Form Integration
		if ( $this->options['show_at_comment_form'] ) {
			$this->comment_form->init();
		}

		// Load WordPress Registration Form Integration
		if ( $this->options['show_at_registration_form'] ) {
			$this->registration_form->init();
		}

		// Load BuddyPress Integration
		if ( $this->options['show_at_buddypress_form'] ) {
			$this->buddypress->init();
		}

		// Load MultiSite Integration
		if ( $this->options['show_at_multisite_form'] ) {
			$this->multisite->init();
		}

		// Load bbPress Integration
		if ( $this->options['show_at_bbpress_forms'] ) {
			$this->bbpress->init();
		}

		// Load CF7 Integration
		if( function_exists( 'wpcf7_add_shortcode' ) ) {
			$this->contact_form_7->init();
		}

		// Load Events Manager integration
		if( defined( 'EM_VERSION' ) ) {
			$this->events_manager->init();
		}

		// Load WooCommerce Integration
		if ( $this->options['show_at_woocommerce_checkout'] ) {
			$this->woocommerce->init();
		}

		// Load EDD Integration
		if ( $this->options['show_at_edd_checkout'] ) {
			$this->easy_digital_downloads->init();
		}

		// load General Integration on POST requests
		if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$this->custom->init();
		}
	}

}