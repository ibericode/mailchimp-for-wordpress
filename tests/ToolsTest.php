<?php

/**
 * Class ToolsTest
 * @ignore
 */
class ToolsTest extends PHPUnit_Framework_TestCase {


	/**
	 *
	 */
	public function test_guess_merge_vars() {

		// Test: Nothing given
		self::assertEquals( mc4wp_guess_merge_vars( array() ), array() );

		// Test: NAME not given
		$input = array(
			'SOME_FIELD' => 'Some value',
			'SOME_OTHER_FIELD' => 'Some other value'
		);
		$expected_output = $input;
		self::assertEquals( mc4wp_guess_merge_vars( $input ), $expected_output );


		// Test: NAME given, LNAME and FNAME expected
		$input = array(
			'NAME' => 'Danny van Kooten'
		);
		$expected_output = array(
			'NAME' => 'Danny van Kooten',
			'FNAME' => 'Danny',
			'LNAME' => 'van Kooten'
		);
		self::assertEquals( mc4wp_guess_merge_vars( $input ), $expected_output );

		// Test: Name without spaces given
		$input = array(
			'NAME' => 'Danny'
		);
		$expected_output = array(
			'NAME' => 'Danny',
			'FNAME' => 'Danny',
		);
		self::assertEquals( mc4wp_guess_merge_vars( $input ), $expected_output );

		// Test: NAME and FNAME given
		$input = array(
			'NAME' => 'Danny',
			'FNAME' => 'Danny',
		);
		$expected_output = $input;
		self::assertEquals( mc4wp_guess_merge_vars( $input ), $expected_output );
	}

}