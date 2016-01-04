<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Custom_Integration
 * @ignore
 */
class MC4WP_Custom_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $checkbox_name = 'mc4wp-subscribe';

	/**
	 * @var string
	 */
	public $name = "Custom";

	/**
	 * @var string
	 */
	public $description = "Integrate with custom third-party forms.";

	/**
	* Add hooks
	*/
	public function add_hooks() {
		add_action( 'init', array( $this, 'listen'), 90 );
	}

	/**
	 * Maybe fire a general subscription request
	 */
	public function listen() {

		if ( ! $this->checkbox_was_checked() ) {
			return false;
		}

		$data = $this->get_data();

		// don't run for CF7 or Events Manager requests
		// (since they use the same "mc4wp-subscribe" trigger)
		$disable_triggers = array(
			'_wpcf7' => '',
			'action' => 'booking_add'
		);

		foreach( $disable_triggers as $trigger => $trigger_value ) {
			if( isset( $data[ $trigger ] ) ) {

				$value = $data[ $trigger ];

				// do nothing if trigger value is optional
				// or if trigger value matches
				if( empty( $trigger_value ) || $value === $trigger_value ) {
					return false;
				}
			}
		}

		// run!
		return $this->process();
	}

	/**
	 * Process custom form
	 *
	 * @return bool|string
	 */
	public function process() {
		$parser = new MC4WP_Field_Guesser( $this->get_data() );
		$data = $parser->combine( array( 'guessed', 'namespaced' ) );

		// do nothing if no email was found
		if( empty( $data['EMAIL'] ) ) {
			return false;
		}

		return $this->subscribe( $data['EMAIL'], $data );
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return true;
	}

	/**
	 * @return array
	 */
	public function get_ui_elements() {
		return array( 'lists', 'double_optin', 'update_existing', 'send_welcome', 'replace_interests' );
	}
}