<?php

/**
 * Class ValidatorTest
 * @ignore
 */
class ValidatorTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var MC4WP_Validator
	 */
	protected $validator;

	public function __construct() {
		parent::__construct();
		$this->validator = new MC4WP_Validator( array( 'foo' => '' ) );
	}

	/**
	 * @covers MC4WP_Validator::__construct
	 */
	public function test_constructor() {
		$fields = array( 'foo' => '' );
		$validator = new MC4WP_Validator( $fields );
		self::assertEquals( $validator->fields, $fields );
	}

	/**
	 * @covers MC4WP_Validator::add_rule
	 */
	public function test_add_rule() {
		$fields = array( 'foo' => '' );

		$rule_array = array(
			'field' => 'foo',
			'rule' => 'not_empty',
			'error_code' => 'error_code',
			'config' => array()
		);

		$validator = new MC4WP_Validator( $fields );
		$validator->add_rule( $rule_array['field'], $rule_array['rule'], $rule_array['error_code'] );
		self::assertEquals( array_pop( $validator->rules ), $rule_array );

		$validator = new MC4WP_Validator( $fields );
		$validator->add_rule( $rule_array['field'], $rule_array['rule'], $rule_array['error_code'], $rule_array['config'] );
		self::assertEquals( array_pop( $validator->rules ), $rule_array );
	}

	/**
	 * @covers MC4WP_Validator::is_empty
	 */
	public function test_is_empty() {
		self::assertTrue( $this->validator->is_empty( '' ) );
		self::assertFalse( $this->validator->is_empty( 'foo' ) );
	}

	/**
	 * @covers MC4WP_Validator::is_not_empty
	 */
	public function test_is_not_empty() {
		self::assertTrue( $this->validator->is_not_empty( 'foo' ) );
		self::assertFalse( $this->validator->is_not_empty( '' ) );
	}

	/**
	 * @covers MC4WP_Validator::is_range
	 */
	public function test_is_range() {
		self::assertTrue( $this->validator->is_range( 5, array( 'min' => 2 ) ) );
		self::assertTrue( $this->validator->is_range( 5, array( 'min' => 2, 'max' => 6 ) ) );

		self::assertFalse( $this->validator->is_range( 5, array( 'min' => 6 ) ) );
		self::assertFalse( $this->validator->is_range( 6, array( 'max' => 5 ) ) );

		self::assertTrue( $this->validator->is_range( 6, array( 'min' => 6, 'max' => 6 ) ) );
	}

	/**
	 * @covers MC4WP_Validator::validate
	 */
	public function test_validate() {
		$fields = array(
			'foo' => 'bar',
			'top' => array(
				'a' => 'value',
				'b' => '',
			)
		);

		$validator = new MC4WP_Validator( $fields );
		self::assertTrue( $validator->validate() );

		$validator->add_rule( 'top.a', 'not_empty' );
		self::assertTrue( $validator->validate() );

		$validator->add_rule( 'top.b', 'empty' );
		self::assertTrue( $validator->validate() );

		$validator->add_rule( 'foo', 'empty' );
		self::assertFalse( $validator->validate() );

		$validator->add_rule( 'top.aaa', 'not_empty' );
		$validator->validate();
	}


}
