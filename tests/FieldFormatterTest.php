<?php


/**
 * Class FieldGuesserTest
 *
 * @ignore
 */
class FieldFormatterTest extends PHPUnit_Framework_TestCase {

	/**
	 * @covers MC4WP_Field_Formatter::address
	 */
	public function test_address() {
		$formatter = new MC4WP_Field_Formatter();

		$address = array(
			'addr1' => "795 E DRAGRAM",
			'addr2' => '',
			'city' => 'TUCSON',
			'state' => 'AZ',
			'zip' => '85705',
			'country' => 'USA'
		);

		// should accept string
		$value = $formatter->address( join( ',', $address ) );
		self::assertArraySubset( $value, $address );
		self::assertEquals( $value['addr1'], $address['addr1'] );
		self::assertEquals( $value['city'], $address['city'] );
		self::assertEquals( $value['state'], $address['state'] );
		self::assertEquals( $value['zip'], $address['zip'] );
		self::assertEquals( $value['country'], $address['country'] );

		// partial string value
		$value = $formatter->address( $address['addr1'] );
		self::assertEquals( $value['addr1'], $address['addr1'] );
		self::assertArrayHasKey( 'city', $value );

		// partial array value
		$value = $formatter->address( array( 'city' => $address['city'] ) );
		self::assertEquals( $value['city'], $address['city'] );
		self::assertArrayHasKey( 'addr1', $value );
	}

	/**
	 * @covers MC4WP_Field_Formatter::birthday
	 */
	public function test_birthday() {
		$formatter = new MC4WP_Field_Formatter();

		$birthday = '07/29';

		// try correct value as-is (mm/dd)
		$value = $formatter->birthday( $birthday );
		self::assertEquals( $birthday, $value );

		// try dd/mm value
		$value = $formatter->birthday( '29/07' );
		self::assertEquals( $birthday, $value );

		// array with "day" and "month"
		$value = $formatter->birthday( array( 'day' => 29, 'month' => 7 ) );
		self::assertEquals( $birthday, $value );

		// simple array
		$value = $formatter->birthday( array( 29, 7 ) );
		self::assertEquals( $birthday, $value );

		// full year
		$value = $formatter->birthday( '1990/7/29' );
		self::assertEquals( $birthday, $value );

        // other seperator
        $value = $formatter->birthday( '1990-07-29' );
        self::assertEquals( $birthday, $value );
	}

	/**
	 * @covers MC4WP_Field_Formatter::date
	 */
	public function test_date() {
		$formatter = new MC4WP_Field_Formatter();

		$date = '2016-05-05';

		$value = $formatter->date( '2016/5/5' );
		self::assertEquals( $date, $value );

		// flipped order
		$value = $formatter->date( '5/5/2016' );
		self::assertEquals( $date, $value );

		// array keys
		$value = $formatter->date( array( 'day' => 5, 'month' => 5, 'year' => 2016 ) );
		self::assertEquals( $date, $value );
	}

    /**
     * @covers MC4WP_Field_Formatter::boolean
     */
    public function test_boolean() {
        $formatter = new MC4WP_Field_Formatter();

        $falsey_tests = array( 'false', '0', 0, false );
        foreach( $falsey_tests as $test ) {
            self::assertEquals( false, $formatter->boolean( $test ) );
        }

        $truthy_tests = array( 'true', '1', 1, true );
        foreach( $truthy_tests as $test ) {
            self::assertEquals( true, $formatter->boolean( $test ) );
        }
    }
}