<?php

// prevent direct file access
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_General_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $type = 'general';

	/**
	 * @var string
	 */
	protected $checkbox_name = 'mc4wp-subscribe';

	/**
	* Constructor
	*/
	public function __construct() {

		// run backwards compatibility routine
		$this->upgrade();

		// hook actions
		add_action( 'init', array( $this, 'maybe_subscribe'), 90 );

	}

	/**
	* Upgrade routine
	*/
	protected function upgrade() {
		// set new $_POST trigger value
		if( isset( $_POST['mc4wp-try-subscribe'] ) ) {
			$_POST[ $this->checkbox_name ] = 1;
			unset( $_POST['mc4wp-try-subscribe'] );
		}

		if( isset( $_POST['mc4wp-do-subscribe'] ) ) {
			$_POST[ $this->checkbox_name ] = 1;
			unset( $_POST['mc4wp-do-subscribe'] );
		}

		if( isset( $_POST['_mc4wp_subscribe'] ) ) {
			$_POST[ $this->checkbox_name ] = 1;
			unset( $_POST['_mc4wp_subscribe'] );
		}
	}

	/**
	 * Maybe fire a general subscription request
	 */
	public function maybe_subscribe() {

		if( $this->is_spam() ) {
			return false;
		}

		if ( ! $this->checkbox_was_checked() ) {
			return false;
		}

		// don't run if this is a CF7 request
		if( isset( $_POST['_wpcf7'] ) ) {
			return false;
		}

		// don't run if this is an Events Manager request
		if( isset( $_POST['action'] ) && $_POST['action'] === 'booking_add' && isset( $_POST['event_id'] ) ) {
			return false;
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

		foreach( $_POST as $key => $value ) {

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

		return $this->subscribe( $email, $merge_vars, $this->type );
	}


}