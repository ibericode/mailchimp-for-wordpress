<?php

class MC4WP_Form_Listener {

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function listen( array $data ) {

		// only act on POST requests
		if( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return false;
		}

		// is form submitted?
		if( ! isset( $data['_mc4wp_form_submit'] ) ) {
			return false;
		}

		$request = new MC4WP_Form_Request( $data );
		$this->process( $request );

		return true;
	}

	/**
	 * @param MC4WP_Form_Request $request
	 *
	 * @return bool
	 */
	public function process( MC4WP_Form_Request $request ) {

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

		$request->respond();

		return $success;
	}

}