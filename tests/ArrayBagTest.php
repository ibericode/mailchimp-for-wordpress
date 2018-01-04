<?php

use PHPUnit\Framework\TestCase;

/**
 * Class ArrayBagTest
 * @ignore
 */
class ArrayBagTest extends TestCase {

	/**
	 * @covers MC4WP_Array_Bag::keys
	 */
	public function test_keys() {
		$array = array(
			'foo' => 'bar',
		);

		$array_bag = new MC4WP_Array_Bag( $array );
		self::assertEquals( $array_bag->keys(), array_keys( $array ) );
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
		self::assertEquals( $array_bag->all(), $array );
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
		self::assertEquals( $array_bag->all_with_prefix('prefixed_'), $result );

		self::assertEmpty( $array_bag->all_with_prefix( '_nothing' ) );

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
		self::assertEquals( $array_bag->all_without_prefix('prefixed_'), $result );
	}

	
}
