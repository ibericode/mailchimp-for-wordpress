<?php

defined( 'ABSPATH' ) or exit;

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
	public $slug = 'custom';

	/**
	 * @var string
	 */
	public $description = "Allows you to integrate with custom third-party forms.";

	/**
	* Add hooks
	*/
	public function add_hooks() {
		add_action( 'init', array( $this, 'maybe_subscribe'), 90 );
	}

	/**
	 * Maybe fire a general subscription request
	 */
	public function maybe_subscribe() {

		if( $this->is_honeypot_filled() ) {
			return false;
		}

		if ( ! $this->checkbox_was_checked() ) {
			return false;
		}

		// don't run for CF7 or Events Manager requests
		// (since they use the same "mc4wp-subscribe" trigger)
		$disable_triggers = array(
			'_wpcf7' => '',
			'action' => 'booking_add'
		);

		foreach( $disable_triggers as $trigger => $trigger_value ) {
			if( isset( $_REQUEST[ $trigger ] ) ) {

				$value = $_REQUEST[ $trigger ];

				if( empty( $trigger_value ) || $value === $trigger_value ) {
					return false;
				}
			}
		}

		// run!
		return $this->try_subscribe();
	}

	/**
	 * Tries to create a sign-up request from the current $_POST data
	 */
	public function try_subscribe() {

		// start running..
		$email = null;
		$merge_vars = array(
			'GROUPINGS' => array()
		);

		foreach( $this->request_data as $key => $value ) {

			if( $key[0] === '_' || $key === $this->checkbox_name ) {
				continue;
			} elseif( strtolower( substr( $key, 0, 6 ) ) === 'mc4wp-' ) {
				// find extra fields which should be sent to MailChimp
				$key = strtoupper( substr( $key, 6 ) );
				$value = ( is_scalar( $value ) ) ? sanitize_text_field( $value ) : $value;

				switch( $key ) {
					case 'EMAIL':
						$email = $value;
					break;

					case 'GROUPINGS':

						$groupings = (array) $value;

						foreach( $groupings as $grouping_id_or_name => $groups ) {

							$grouping = array();

							// group ID or group name given?
							if(is_numeric( $grouping_id_or_name ) ) {
								$grouping['id'] = absint( $grouping_id_or_name );
							} else {
								$grouping['name'] = sanitize_text_field( stripslashes( $grouping_id_or_name ) );
							}

							// comma separated list should become an array
							if( ! is_array( $groups ) ) {
								$groups = explode( ',', sanitize_text_field( $groups ) );
							}

							$grouping['groups'] = array_map( 'stripslashes', $groups );

							// add grouping to array
							$merge_vars['GROUPINGS'][] = $grouping;

						} // end foreach $groupings
					break;

					default:
						if( is_array( $value ) ) {
							$value = sanitize_text_field( implode( ',', $value ) );
						}

						$merge_vars[$key] = $value;
					break;
				}

			} elseif( ! $email && is_string( $value ) && is_email( $value ) ) {
				// if no email is found yet, check if current field value is an email
				$email = $value;
			} elseif( ! $email && is_array( $value ) && isset( $value[0] ) && is_string( $value[0] ) && is_email( $value[0] ) ) {
				// if no email is found yet, check if current value is an array and if first array value is an email
				$email = $value[0];
			} else {
				$simple_key = str_replace( array( '-', '_' ), '', strtolower( $key ) );

				if( ! $email && in_array( $simple_key, array( 'email', 'emailaddress', 'contactemail' ) ) ) {
					$email = $value;
				} elseif( ! isset( $merge_vars['NAME'] ) && in_array( $simple_key, array( 'name', 'yourname', 'username', 'fullname', 'contactname' ) ) ) {
					// find name field
					$merge_vars['NAME'] = $value;
				} elseif( ! isset( $merge_vars['FNAME'] ) && in_array( $simple_key, array( 'firstname', 'fname', 'givenname', 'forename' ) ) ) {
					// find first name field
					$merge_vars['FNAME'] = $value;
				} elseif( ! isset( $merge_vars['LNAME'] ) && in_array( $simple_key, array( 'lastname', 'lname', 'surname', 'familyname' ) ) ) {
					// find last name field
					$merge_vars['LNAME'] = $value;
				}
			}
		}

		// unset groupings if not used
		if( empty( $merge_vars['GROUPINGS'] ) ) {
			unset( $merge_vars['GROUPINGS'] );
		}

		// if email has not been found by the smart field guessing, return false.. Sorry
		if ( ! $email ) {
			return false;
		}

		return $this->subscribe( $email, $merge_vars );
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
		return array( 'lists', 'double_optin', 'update_existing', 'send_welcome' );
	}
}