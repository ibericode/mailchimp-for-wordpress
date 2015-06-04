<?php

class MC4WP_API_Response {

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var
	 */
	public $success;

	/**
	 * @var stdClass
	 */
	public $response_raw;

	/**
	 * @var string subscribed|unsubscribed|already_subscribed|not_subscribed
	 */
	public $code = 'error';

	/**
	 * @var string
	 */
	public $message = '';

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
		$this->message = ( isset( $response->message ) ) ? $response->message : '';
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