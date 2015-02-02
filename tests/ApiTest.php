<?php

// Load class into memory
define( 'MC4WP_LITE_VERSION', 1 );
require_once __DIR__ . '/../includes/class-api.php';

// Debug class used for testing, no remote requests..
class ApiDebug extends MC4WP_API {

	/**
	 * Default response
	 *
	 * @var bool
	 */
	private $response = false;

	/**
	 * Overwrite `call` method to just store test reponse right away.
	 */
	public function call( $method, array $data = array() ) {
		return $this->response;
	}

	/**
	 * Test helper to set the API response in an easy way.
	 *
	 * @param  $response
	 */
	public function set_response( $response ) {
		$this->response = $response;
	}

}

class ApiTest extends PHPUnit_Framework_TestCase {

	public function test_is_connected() {

		// no api key, false
		$api = new ApiDebug( '' );
		$this->assertFalse( $api->is_connected() );

		// correct response, true
		$api = new ApiDebug( 'apikey' );
		$api->set_response( (object) array( 'msg' => 'Everything\'s Chimpy!' ) );
		$this->assertTrue( $api->is_connected() );

		// failed request, false
		$api = new ApiDebug( 'apikey' );
		$api->set_response( false );
		$this->assertFalse( $api->is_connected() );
	}
}