<?php

class MC4WP_Form_Listener {

	public function listen() {
		// has a MC4WP form been submitted?
		if ( isset( $_POST['_mc4wp_form_submit'] ) ) {
			$request = new MC4WP_Lite_Form_Request( $_POST );
			$this->process( $request );
			return true;
		}

		return false;
	}

	/**
	 * @param MC4WP_Lite_Form_Request $request
	 *
	 * @return bool
	 */
	public function process( MC4WP_Lite_Form_Request $request ) {

		if( $request->validate() ) {

			// prepare request data
			$request->prepare();

			// if request is ready, send an API call to MailChimp
			if( $request->ready ) {
				$request->subscribe();
			}
		}

		$request->send_http_response();

		return $request->success;
	}

}