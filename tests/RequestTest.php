<?php

/**
 * Class RequestTest
 * @ignore
 */
class RequestTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_Request::create_from_globals
	 */
	public function test_create_from_globals() {
		$_GET = array( 'foo' => 'bar' );
		$_POST = array( 'foo2' => 'bar2' );

		$request = MC4WP_Request::create_from_globals();
		self::assertInstanceOf( 'MC4WP_Array_Bag', $request->params );
		self::assertInstanceOf( 'MC4WP_Array_Bag', $request->server );
		self::assertEquals( $request->params->get('foo'), $_GET['foo'] );
		self::assertEquals( $request->params->get('foo2'), $_POST['foo2'] );
	}

	/**
	 * @covers MC4WP_Request::is_ajax
	 */
	public function test_is_ajax() {
		$request = new MC4WP_Request( array() );
		self::assertFalse( $request->is_ajax());

		define( 'DOING_AJAX', true );
		self::assertTrue( $request->is_ajax() );
	}

	/**
	 * @covers MC4WP_Request::is_method
	 */
	public function test_is_method() {
		$request = new MC4WP_Request( array(), array(), array( 'REQUEST_METHOD' => 'POST' ) );

		self::assertFalse( $request->is_method( 'GET' ) );
		self::assertTrue( $request->is_method( 'POST' ) );
	}


}