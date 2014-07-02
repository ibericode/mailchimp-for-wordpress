<?php

// prevent direct file access
if( ! defined( "MC4WP_LITE_VERSION" ) ) {
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
	private function upgrade() {
		// set new $_POST trigger value
		if( isset( $_POST['mc4wp-try-subscribe'] ) ) {
			$_POST[ $this->checkbox_name ] = 1;
			unset( $_POST['mc4wp-try-subscribe'] );
		}

		if( isset( $_POST['mc4wp-do-subscribe'] ) ) {
			$_POST[ $this->checkbox_name ] = 1;
			unset( $_POST['mc4wp-do-subscribe'] );
		}
	}

	/**
	 * Maybe fire a general subscription request
	 */
	public function maybe_subscribe() {

		if ( $this->checkbox_was_checked() === false ) {
			return;
		}

		// don't run if this is a CF7 request
		if( isset( $_POST['_wpcf7'] ) ) {
			return false;
		}

		$this->try_subscribe( 'other_form' );
	}

	/**
	 * @return boolean
	 */
	public function checkbox_was_checked() {

		if( $this->is_honeypot_filled() ) {
			return false;
		}

		if( isset( $_POST[ '_mc4wp_subscribe' ] ) && $_POST[ '_mc4wp_subscribe' ] == 1 ) {
			return true;
		}

		return ( isset( $_POST[ $this->checkbox_name ] ) && $_POST[ $this->checkbox_name ] == 1 );
	}

	/**
	* Tries to subscribe from any third-party form (and CF7)
	*
	* @param string $trigger
	*/	
	public function try_subscribe( $trigger = 'other_form' ) {

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
				$value = ( is_scalar( $value ) ) ? trim( $value ) : $value;

				switch( $key ) {
					case 'EMAIL':
						$email = $value;
					break;

					case 'GROUPINGS':
						$groupings = $value;

						foreach($groupings as $grouping_id_or_name => $groups) {

							$grouping = array();

							// group ID or group name given?
							if(is_numeric($grouping_id_or_name)) {
								$grouping['id'] = $grouping_id_or_name;
							} else {
								$grouping['name'] = stripslashes( $grouping_id_or_name );
							}

							// comma separated list should become an array
							if( ! is_array( $groups ) ) {
								$groups = explode( ',', $groups );
							}
						
							$grouping['groups'] = array_map( 'stripslashes', $groups );

							// add grouping to array
							$merge_vars['GROUPINGS'][] = $grouping;

						} // end foreach $groupings
					break;

					default:
						if( is_array( $value ) ) { 
							$value = implode( ',', $value ); 
						}

						$merge_vars[$key] = $value;
					break;
				}


			} elseif( ! $email && is_email( $value ) ) {
				// find first email field
				$email = $value;
			} else {
				$simple_key = str_replace( array( '-', '_' ), '', strtolower( $key ) );

				if( ! isset( $merge_vars['NAME'] ) && in_array( $simple_key, array( 'name', 'yourname', 'username', 'fullname' ) ) ) {
					// find name field
					$merge_vars['NAME'] = $value;
				} elseif( ! isset( $merge_vars['FNAME'] ) && in_array( $simple_key, array( 'firstname', 'fname', "givenname", "forename" ) ) ) {
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

		return $this->subscribe( $email, $merge_vars, $trigger );
	}


}