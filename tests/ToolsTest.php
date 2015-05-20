<?php

require_once __DIR__ . '/mock.php';
require_once __DIR__ . '/../includes/class-tools.php';

class ToolsTest extends PHPUnit_Framework_TestCase {


	/**
	 * @covers MC4WP_Tools::guess_merge_vars
	 */
	public function test_guess_merge_vars() {

		// Test: Nothing given
		$this->assertEquals( MC4WP_Tools::guess_merge_vars( array() ), array() );

		// Test: NAME not given
		$input = array(
			'SOME_FIELD' => 'Some value',
			'SOME_OTHER_FIELD' => 'Some other value'
		);
		$expected_output = $input;
		$this->assertEquals( MC4WP_Tools::guess_merge_vars( $input ), $expected_output );


		// Test: NAME given, LNAME and FNAME expected
		$input = array(
			'NAME' => 'Danny van Kooten'
		);
		$expected_output = array(
			'NAME' => 'Danny van Kooten',
			'FNAME' => 'Danny',
			'LNAME' => 'van Kooten'
		);
		$this->assertEquals( MC4WP_Tools::guess_merge_vars( $input ), $expected_output );

		// Test: Name without spaces given
		$input = array(
			'NAME' => 'Danny'
		);
		$expected_output = array(
			'NAME' => 'Danny',
			'FNAME' => 'Danny',
		);
		$this->assertEquals( MC4WP_Tools::guess_merge_vars( $input ), $expected_output );

		// Test: NAME and FNAME given
		$input = array(
			'NAME' => 'Danny',
			'FNAME' => 'Danny',
		);
		$expected_output = $input;
		$this->assertEquals( MC4WP_Tools::guess_merge_vars( $input ), $expected_output );
	}

	/**
	 * @covers MC4WP_Tools::get_known_email
	 */
	public function test_get_known_email() {

		// Test: No known email
		$this->assertEmpty( MC4WP_Tools::get_known_email() );

		// Test: Email given in query string
		$_REQUEST['mc4wp_email'] = 'johndoe@email.com';
		$this->assertEquals( MC4WP_Tools::get_known_email(), $_REQUEST['mc4wp_email'] );
	}
}