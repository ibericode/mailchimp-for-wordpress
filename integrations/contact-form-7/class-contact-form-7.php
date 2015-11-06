<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Contact_Form_7_Integration
 *
 * @ignore
 */
class MC4WP_Contact_Form_7_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	public $name = "Contact Form 7";

	/**
	 * @var string
	 */
	public $description = "Allows you to subscribe people from your Contact Form 7 forms.";


	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'wpcf7_init', array( $this, 'init') );
		add_action( 'wpcf7_mail_sent', array( $this, 'subscribe_from_cf7' ) );
		add_action( 'wpcf7_posted_data', array( $this, 'alter_cf7_data') );
	}

	/**
	* Registers the CF7 shortcode
	 *
	* @return boolean
	*/
	public function init() {
		wpcf7_add_shortcode( 'mc4wp_checkbox', array( $this, 'shortcode' ) );
		return true;
	}

	/**
	* Alter Contact Form 7 data.
	*
	* Adds mc4wp_checkbox to post data so users can use `mc4wp_checkbox` in their email templates
	*
	* @param array $data
	* @return array
	*/
	public function alter_cf7_data( $data = array() ) {
		$data['mc4wp_checkbox'] = $this->checkbox_was_checked() ? __( 'Yes', 'mailchimp-for-wp' ) : __( 'No', 'mailchimp-for-wp' );
		return $data;
	}

	/**
	 * Subscribe from Contact Form 7 Forms
	 * @todo improve smart guessing based on selected MailChimp lists
	 * @todo groupings data still need to be formatted. Do that on global Integration level?
	 * @return bool
	 */
	public function subscribe_from_cf7() {

		// was sign-up checkbox checked?
		if ( ! $this->checkbox_was_checked() ) {
			return false;
		}

		$request_data = stripslashes_deep( $_REQUEST );
		$parser = new MC4WP_Data_Parser( $request_data );
		$data = $parser->combine( array( 'guessed', 'namespaced' ) );

		// do nothing if no email was found
		if( empty( $data['EMAIL'] ) ) {
			return false;
		}

		return $this->subscribe( $data['EMAIL'], $data );
	}

	/**
	 * Return the shortcode output
	 *
	 * @return string
	 */
	public function shortcode( $args = array() ) {

		if ( ! empty( $args['labels'][0] ) ) {
			$this->options['label'] = $args['labels'][0];
		}

		if( isset( $args['options'] ) ) {
			// check for default:0 or default:1 to set the checked attribute
			if( in_array( 'default:1', $args['options'] ) ) {
				$this->options['precheck'] = true;
			} else if( in_array( 'default:0', $args['options'] ) ) {
				$this->options['precheck'] = false;
			}
		}

		return $this->get_checkbox_html();
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return function_exists( 'wpcf7_add_shortcode' );
	}

	public function get_ui_elements() {
		return array_diff( parent::get_ui_elements(), array( 'enabled' ) );
	}

}