<?php

class ArrayBagTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_Array_Bag::keys
	 */
	public function test_keys() {
		$array = array(
			'foo' => 'bar',
		);

		$array_bag = new MC4WP_Array_Bag( $array );
		$this->assertEquals( $array_bag->keys(), array_keys( $array ) );
	}

	/**
	 * @covers MC4WP_Array_Bag::all
	 */
	public function test_all() {
		$array = array(
			'key1' => 'foo',
			'key2' => 'bar'
		);
		$array_bag = new MC4WP_Array_Bag( $array );
		$this->assertEquals( $array_bag->all(), $array );

		$array_uppercased = array_change_key_case( $array, CASE_UPPER );
		$this->assertEquals( $array_bag->all( CASE_UPPER ), $array_uppercased );
	}

	/**
	 * @covers MC4WP_Array_Bag::all_with_prefix
	 */
	public function test_all_with_prefix() {
		$array = array(
			'prefixed_key1' => 'foo',
			'prefixed_key2' => 'bar',
			'key3' => 'foobar'
		);
		$result = array(
			'key1' => 'foo',
			'key2' => 'bar'
		);

		$array_bag = new MC4WP_Array_Bag( $array );
		$this->assertEquals( $array_bag->all_with_prefix('prefixed_'), $result );

		$this->assertEmpty( $array_bag->all_with_prefix( '_nothing' ) );

		$array_bag = new MC4WP_Array_Bag( $array );
		$result_uppercased = array_change_key_case( $result, CASE_UPPER );
		$this->assertEmpty( $array_bag->all_with_prefix( '_prefixed', CASE_UPPER ) );
		$this->assertEquals( $array_bag->all_with_prefix( 'PREFIXED_', CASE_UPPER ), $result_uppercased );

	}

	/**
	 * @covers MC4WP_Array_Bag::all_without_prefix
	 */
	public function test_all_without_prefix() {
		$array = array(
			'prefixed_key1' => 'foo',
			'prefixed_key2' => 'bar',
			'key3' => 'foobar'
		);
		$result = array(
			'key3' => 'foobar'
		);
		$array_bag = new MC4WP_Array_Bag( $array );
		$this->assertEquals( $array_bag->all_without_prefix('prefixed_'), $result );

		$array_uppercased = array_change_key_case( $array, CASE_UPPER );
		$result_uppercased = array_change_key_case( $result, CASE_UPPER );
		$this->assertEquals( $array_bag->all_without_prefix( 'PREFIXED_', CASE_UPPER ), $result_uppercased );
		$this->assertEquals( $array_bag->all_without_prefix( 'prefixed_', CASE_UPPER ), $array_uppercased );
	}

	
}