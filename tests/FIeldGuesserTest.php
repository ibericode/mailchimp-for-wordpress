<?php

/**
 * Class FieldGuesserTest
 *
 * @ignore
 */
class FieldGuesserTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_Field_Guesser::namespaced
	 */
	public function test_namespaced() {
		$data = array(
			'prefix-foo' => 'bar',
			'foo' => 'barbar'
		);

		$instance = new MC4WP_Field_Guesser( $data );
		self::assertEquals( array(
			'FOO' => 'bar'
			),
			$instance->namespaced( 'prefix-' )
		);
	}

	/**
	 * @covers MC4WP_Field_Guesser::guessed
	 */
	public function test_guessed() {
		$data = array(
			'name' => 'Danny van Kooten',
			'first-name' => 'Danny',
			'lname' => 'van Kooten'
		);

		$instance = new MC4WP_Field_Guesser( $data );
		self::assertEquals( array(
				'NAME' => 'Danny van Kooten',
				'FNAME' => 'Danny',
				'LNAME' => "van Kooten"
			),
			$instance->guessed()
		);
	}

	/**
	 * @covers MC4WP_Field_Guesser::combine
	 */
	public function test_combine() {
		$data = array(
			'name' => 'Danny van Kooten',
			'prefix-email' => 'johndoe@email.com',
			'foo' => 'bar'
		);

		$instance = new MC4WP_Field_Guesser( $data );
		$result = $instance->combine(array( 'namespaced', 'guessed' ));

		self::assertEquals( $result['NAME'], $data['name'] );
		self::assertEquals( $result['EMAIL'], $data['prefix-email'] );
		self::assertArrayNotHasKey( 'foo', $result );

		// test order (latter overwrites former)
		$data = array(
			'name' => 'Danny van Kooten',
			'mc4wp-name' => 'Danny Janssen'
		);

		$instance = new MC4WP_Field_Guesser( $data );
		$result = $instance->combine(array( 'namespaced', 'guessed' ));
		self::assertEquals( $result['NAME'], $data['name'] );

		$result = $instance->combine(array( 'guessed', 'namespaced' ));
		self::assertEquals( $result['NAME'], $data['mc4wp-name'] );


	}

}