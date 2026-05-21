<?php

use PHPUnit\Framework\TestCase;

/**
 * Class Functions_Test
 * @ignore
 */
class FunctionsTest extends TestCase
{
    public $tests = [
        [
            'input' => [],
            'output' => [],
        ],
        [
            'input' => [
                'SOME_FIELD' => 'Some value',
                'SOME_OTHER_FIELD' => 'Some other value'
            ],
            'output' => [
                'SOME_FIELD' => 'Some value',
                'SOME_OTHER_FIELD' => 'Some other value'
            ],
        ],
        [
            'input' => [
                'NAME' => 'Danny van Kooten'
            ],
            'output' => [
                'NAME' => 'Danny van Kooten',
                'FNAME' => 'Danny',
                'LNAME' => 'van Kooten'
            ],
        ],
        [
            'input' => [
                'NAME' => 'Danny'
            ],
            'output' => [
                'NAME' => 'Danny',
                'FNAME' => 'Danny',
            ],
        ],
    ];


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
     * @covers mc4wp_obfuscate_email_addresses()
     * @dataProvider email_address_obfuscation_provider
     */
    public function test_mc4wp_obfuscate_email_addresses_handles_common_valid_addresses($email_address, $expected)
    {
        $obfuscated = mc4wp_obfuscate_email_addresses($email_address);

        self::assertEquals($expected, $obfuscated);
    }

    public function email_address_obfuscation_provider()
    {
        return [
            [ 'john.doe+tag@gmail.com', 'john****+tag@gma***com' ],
            [ 'john-doe@gmail.com', 'jo****oe@gma***com' ],
            [ 'test_alias@example.com', 'tes****ias@exa*****com' ],
            [ 'test@example-domain.com', 't**t@exam**********.com' ],
            [ 'test@example.co.uk', 't**t@exam*****o.uk' ],
            [ 'test@example.travel', 't**t@exam******avel' ],
        ];
    }

    /**
     * @covers mc4wp_obfuscate_string
     */
    public function test_mc4wp_obfuscate_string()
    {
        self::assertEquals('', mc4wp_obfuscate_string(''));
        self::assertEquals('a', mc4wp_obfuscate_string('a'));
        self::assertEquals('aa', mc4wp_obfuscate_string('aa'));
        self::assertEquals('a*a', mc4wp_obfuscate_string('aaa'));
        self::assertEquals('a**a', mc4wp_obfuscate_string('aaaa'));
        self::assertEquals('abcd****************************-us1', mc4wp_obfuscate_string('abcdefghijklmnopqrstuvwxyzabcdef-us1'));

        // by no means should the two strings be similar
        $string = 'super-secret-string';
        $obfuscated = mc4wp_obfuscate_string($string);
        self::assertNotEquals($string, $obfuscated);

        // less than 50% of the string should be similar
        similar_text($string, $obfuscated, $percentage);
        self::assertTrue($percentage <= 50);
    }

    /**
     * @covers mc4wp_truncate_log_message
     */
    public function test_mc4wp_truncate_log_message()
    {
        $message = str_repeat('a', 8193);
        $truncated = mc4wp_truncate_log_message($message);

        self::assertEquals(8192, strlen($truncated));
        self::assertStringEndsWith('... [truncated, original length: 8193 bytes]', $truncated);
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
     * @covers mc4wp_array_get
     */
    public function test_mc4wp_array_get()
    {
        self::assertEquals(mc4wp_array_get([ 'foo' => 'bar' ], 'foo'), 'bar');
        self::assertEquals(mc4wp_array_get([ 'foo' => 'bar' ], 'foofoo', 'default'), 'default');
        self::assertEquals(mc4wp_array_get([ 'foo' => [ 'bar' => 'foobar' ] ], 'foo.bar'), 'foobar');
        self::assertEquals(mc4wp_array_get([ 'foo' => [ 'bar' => 'foobar' ] ], 'foo.foo', 'default'), 'default');
    }

    /**
     * @covers mc4wp_sanitize_deep
     */
    public function test_mc4wp_sanitize_deep()
    {
        self::assertEquals('John & Jane', mc4wp_sanitize_deep(' <strong>John &amp; Jane</strong> '));
        self::assertEquals('John "Nickname" Doe', mc4wp_sanitize_deep('John \"Nickname\" Doe'));
        self::assertEquals(str_repeat('a', 1024), mc4wp_sanitize_deep(str_repeat('a', 1025)));

        self::assertSame(123, mc4wp_sanitize_deep(123));
        self::assertSame(false, mc4wp_sanitize_deep(false));
        self::assertSame(null, mc4wp_sanitize_deep(null));
    }

    /**
     * @covers mc4wp_sanitize_deep
     */
    public function test_mc4wp_sanitize_deep_recurses_into_arrays_and_objects()
    {
        $object        = new stdClass();
        $object->name  = ' <em>Jane</em> ';
        $object->quote = 'Jane \"Nickname\" Doe';

        $input = [
            'html_key' => ' <strong>John</strong> ',
            'nested'   => [
                'entity' => 'Tom &amp; Jerry',
                'long'   => str_repeat('b', 1025),
            ],
            'object'   => $object,
        ];

        $sanitized = mc4wp_sanitize_deep($input);

        self::assertEquals('John', $sanitized['html_key']);
        self::assertEquals('Tom & Jerry', $sanitized['nested']['entity']);
        self::assertEquals(str_repeat('b', 1024), $sanitized['nested']['long']);
        self::assertEquals('Jane', $sanitized['object']->name);
        self::assertEquals('Jane "Nickname" Doe', $sanitized['object']->quote);
        self::assertArrayHasKey('html_key', $sanitized);
    }

    /**
     * @covers mc4wp_is_email
     */
    public function test_mc4wp_is_email()
    {
        self::assertTrue(mc4wp_is_email('john@example.com'));
        self::assertTrue(mc4wp_is_email('john.doe+tag@example.co.uk'));

        self::assertFalse(mc4wp_is_email(''));
        self::assertFalse(mc4wp_is_email('not-an-email'));
        self::assertFalse(mc4wp_is_email('john@example'));
        self::assertFalse(mc4wp_is_email('john@@example.com'));
        self::assertFalse(mc4wp_is_email('john@example.com '));
        self::assertFalse(mc4wp_is_email(' john@example.com'));

        self::assertFalse(mc4wp_is_email(null));
        self::assertFalse(mc4wp_is_email(false));
        self::assertFalse(mc4wp_is_email(123));
        self::assertFalse(mc4wp_is_email([]));
        self::assertFalse(mc4wp_is_email((object) []));

        $email = str_repeat('a', 65) . '@' . str_repeat('b', 63) . '.' . str_repeat('c', 63) . '.' . str_repeat('d', 63) . '.' . str_repeat('e', 63);
        self::assertSame(321, strlen($email));
        self::assertFalse(mc4wp_is_email($email));
    }

    public function test_mc4wp_get_request_ip_address()
    {
        $_SERVER = [ ];
        self::assertEquals('', mc4wp_get_request_ip_address());

        $_SERVER = [ 'REMOTE_ADDR' => '127.0.0.1' ];
        self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address());

        $_SERVER = [ 'HTTP_X_FORWARDED_FOR' => '127.0.0.1', 'REMOTE_ADDR' => '1.1.1.1' ];
        self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address());

        $_SERVER = [ 'X-Forwarded-For' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '1.1.1.2', 'REMOTE_ADDR' => '1.1.1.1' ];
        self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address());

        $_SERVER = [ 'X-Forwarded-For' => '127.0.0.1,127.0.0.2', 'HTTP_X_FORWARDED_FOR' => '1.1.1.2', 'REMOTE_ADDR' => '1.1.1.1' ];
        self::assertEquals('127.0.0.1', mc4wp_get_request_ip_address());

        $_SERVER = [ 'X-Forwarded-For' => '127.0.0.1:5000,127.0.0.2', 'HTTP_X_FORWARDED_FOR' => '1.1.1.2', 'REMOTE_ADDR' => '1.1.1.1' ];
        self::assertEquals(null, mc4wp_get_request_ip_address());

        $_SERVER = [ 'REMOTE_ADDR' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ];
        self::assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', mc4wp_get_request_ip_address());

        $_SERVER = [ 'X-Forwarded-For' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334' ];
        self::assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', mc4wp_get_request_ip_address());

        $_SERVER = [ 'X-Forwarded-For' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334:5000' ];
        self::assertEquals(null, mc4wp_get_request_ip_address());
    }
}
