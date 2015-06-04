<?php

class MC4WP_API_Response {

	/**
	 * @var string subscribe|unsubscribe
	 */
	public $type;

	/**
	 * @var bool
	 */
	public $success;

	/**
	 * @var stdClass
	 */
	public $response_raw;

	/**
	 * @var string subscribed|unsubscribed|already_subscribed|not_subscribed
	 */
	public $code = '';

	/**
	 * @var string
	 */
	public $error = '';

	/**
	 * @param string $type
	 * @param bool $success
	 * @param stdClass $response
	 */
	public function __construct( $type, $success, stdClass $response ) {
		$this->type = $type;
		$this->success = $success;
		$this->response_raw = $response;
		$this->code = $this->translate_response_code( $response );
		$this->error = ( $this->code === 'error' && isset( $response->error ) ) ? $response->error : '';
	}

	/**
	 * Translate MailChimp response to a response code (string) we understand
	 *
	 * @param $response
	 *
	 * @return string
	 */
	public function translate_response_code( $response ) {

		if( $this->success ) {
			return $this->type . 'd';
		}

		// convert response codes
		if( isset( $response->code ) ) {
			switch( $response->code ) {

				case 214:
					return 'already_subscribed';
					break;

				case 215:
				case 232:
					return 'not_subscribed';
					break;
			}
		}


		return 'error';
	}

}