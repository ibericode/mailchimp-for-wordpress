<?php
use PHPUnit\Framework\TestCase;

/**
 * Class Functions_Test
 * @ignore
 */
class FunctionsTest extends TestCase
{
    public $tests = array(
        array(
            'input' => array(),
            'output' => array(),
        ),
        array(
            'input' => array(
                'SOME_FIELD' => 'Some value',
                'SOME_OTHER_FIELD' => 'Some other value'
            ),
            'output' => array(
                'SOME_FIELD' => 'Some value',
                'SOME_OTHER_FIELD' => 'Some other value'
            ),
        ),
        array(
            'input' => array(
                'NAME' => 'Danny van Kooten'
            ),
            'output' => array(
                'NAME' => 'Danny van Kooten',
                'FNAME' => 'Danny',
                'LNAME' => 'van Kooten'
            ),
        ),
        array(
            'input' => array(
                'NAME' => 'Danny'
            ),
            'output' => array(
                'NAME' => 'Danny',
                'FNAME' => 'Danny',
            ),
        ),
    );


    /**
     * @covers mc4wp_obfuscate_email_addresses()
     */
    public function test_mc4wp_obfuscate_email_addresses()
    {

        // by no means should the two strings be similar
        $string = 'Mailchimp API error: Recipient "johnnydoe@gmail.com" has too many recent signup requests';
        $obfuscated = mc4wp_obfuscate_email_addresses($string);
        self::assertNotEquals($string, $obfuscated);

        // less than 70% of the string should be similar
        $string = 'johnnydoe@gmail.com';
        $obfuscated = mc4wp_obfuscate_email_addresses($string);
        similar_text($string, $obfuscated, $percentage);
        self::assertTrue($percentage <= 70);
    }

    /**
     * @covers mc4wp_obfuscate_string
     */
    public function test_mc4wp_obfuscate_string()
    {

        // by no means should the two strings be similar
        $string = 'super-secret-string';
        $obfuscated = mc4wp_obfuscate_string($string);
        self::assertNotEquals($string, $obfuscated);

        // less than 50% of the string should be similar
        similar_text($string, $obfuscated, $percentage);
        self::assertTrue($percentage <= 50);
    }

    /**
     * @covers mc4wp_add_name_data
     */
    public function test_mc4wp_add_name_data()
    {
        foreach ($this->tests as $test) {
            self::assertEquals(mc4wp_add_name_data($test['input']), $test['output']);
        }
    }

    /**
     * @covers mc4wp_guess_merge_vars
     */
    public function test_mc4wp_guess_merge_vars()
    {
        foreach ($this->tests as $test) {
            self::assertEquals(mc4wp_guess_merge_vars($test['input']), $test['output']);
        }
    }

    /**
     * @covers mc4wp_array_get
     */
    public function test_mc4wp_array_get()
    {
        self::assertEquals(mc4wp_array_get(array( 'foo' => 'bar' ), 'foo'), 'bar');
        self::assertEquals(mc4wp_array_get(array( 'foo' => 'bar' ), 'foofoo', 'default'), 'default');
        self::assertEquals(mc4wp_array_get(array( 'foo' => array( 'bar' => 'foobar' ) ), 'foo.bar'), 'foobar');
        self::assertEquals(mc4wp_array_get(array( 'foo' => array( 'bar' => 'foobar' ) ), 'foo.foo', 'default'), 'default');
    }

	public function test_mc4wp_get_request_ip_address()
	{
		$_SERVER = array( );
		self::assertEquals('', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'REMOTE_ADDR' => '127.0.0.1' );
		self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'HTTP_X_FORWARDED_FOR' => '127.0.0.1', 'REMOTE_ADDR' => '1.1.1.1' );
		self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'X-Forwarded-For' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '1.1.1.2', 'REMOTE_ADDR' => '1.1.1.1' );
		self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'X-Forwarded-For' => '127.0.0.1,127.0.0.2', 'HTTP_X_FORWARDED_FOR' => '1.1.1.2', 'REMOTE_ADDR' => '1.1.1.1' );
		self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'X-Forwarded-For' => '127.0.0.1:5000,127.0.0.2', 'HTTP_X_FORWARDED_FOR' => '1.1.1.2', 'REMOTE_ADDR' => '1.1.1.1' );
		self::assertEquals(null, mc4wp_get_request_ip_address() );

		$_SERVER = array( 'REMOTE_ADDR' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334' );
		self::assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'X-Forwarded-For' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334' );
		self::assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', mc4wp_get_request_ip_address() );

		$_SERVER = array( 'X-Forwarded-For' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334:5000' );
		self::assertEquals(null, mc4wp_get_request_ip_address() );
	}
}
