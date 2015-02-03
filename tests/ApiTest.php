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

	private $api;

	/**
	 * Before each test
	 */
	public function setUp() {
		$this->api = new ApiDebug('api_key');
	}

	/**
	 * @covers MC4WP_Api::is_connected
	 */
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

	/**
	 * @covers MC4WP_API::subscribe
	 */
	public function test_subscribe() {
		// test request error
		$this->api->set_response( false );
		$this->assertEquals( $this->api->subscribe( 'sample_list_id', 'sample_email' ), 'error' );

		// test "already_subscribed" API error
		$this->api->set_response( (object) array( 'error' => 'error message', 'code' => '214' ) );
		$this->assertEquals( $this->api->subscribe( 'sample_list_id', 'sample_email' ), 'already_subscribed' );

		// test general API errors
		$this->api->set_response( (object) array( 'error' => 'error message', 'code' => '-99' ) );
		$this->assertEquals( $this->api->subscribe( 'sample_list_id', 'sample_email' ), 'error' );

		// test success
		$this->api->set_response( (object) array( 'email' => 'sample_email', 'euid' => 'sample_euid', 'leid' => 'sample_leid' ) );
		$this->assertTrue( $this->api->subscribe( 'sample_list_id', 'sample_email' ) );
	}

	/**
	 * @covers MC4WP_API::get_lists
	 */
	public function test_get_lists() {
		// test error
		$this->api->set_response( false );
		$this->assertFalse( $this->api->get_lists() );

		// test api error
		$this->api->set_response( (object) array( 'error' => 'Error message', 'code' => -99 ) );
		$this->assertFalse( $this->api->get_lists() );

		// test success
		$lists = array( 'sample_list' );
		$this->api->set_response( (object) array( 'data' => $lists ) );
		$this->assertEquals( $this->api->get_lists(), $lists );
	}
}