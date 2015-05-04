<?php

class MC4WP_Form_Listener {

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function listen( array $data ) {

		if( ! isset( $data['_mc4wp_form_submit'] ) ) {
			return false;
		}

		if ( ! isset( $data['_mc4wp_action'] ) || $data['_mc4wp_action'] === 'subscribe' ) {
			$request = new MC4WP_Subscribe_Request( $data );
			$this->process( $request );
		}


		if ( isset( $data['_mc4wp_action'] ) && $data['_mc4wp_action'] === 'unsubscribe' ) {
			$request = new MC4WP_Unsubscribe_Request( $data );
			$this->process( $request );
		}

		return true;
	}

	/**
	 * @param iMC4WP_Request $request
	 *
	 * @return bool
	 */
	public function process( iMC4WP_Request $request ) {

		$valid = $request->validate();
		$success = false;

		if( $valid ) {

			// prepare request data
			$ready = $request->prepare();

			// if request is ready, send an API call to MailChimp
			if( $ready ) {
				$success = $request->process();
			}
		}

		$request->respond( $success );

		return $success;
	}

}