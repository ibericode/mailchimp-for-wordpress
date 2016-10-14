<?php

/**
 * Class Functions_Test
 * @ignore
 */
class Functions_Test extends PHPUnit_Framework_TestCase {

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
    public function test_mc4wp_obfuscate_email_addresses() {

        // by no means should the two strings be similar
        $string = 'MailChimp API error: Recipient "johnnydoe@gmail.com" has too many recent signup requests';
        $obfuscated = mc4wp_obfuscate_email_addresses( $string );
        self::assertNotEquals( $string, $obfuscated );

        // less than 70% of the string should be similar
        $string = 'johnnydoe@gmail.com';
        $obfuscated = mc4wp_obfuscate_email_addresses( $string );
        similar_text( $string, $obfuscated, $percentage );
        self::assertTrue( $percentage <= 70 );
    }

    /**
     * @covers mc4wp_obfuscate_string
     */
    public function test_mc4wp_obfuscate_string() {

        // by no means should the two strings be similar
        $string = 'super-secret-string';
        $obfuscated = mc4wp_obfuscate_string( $string );
        self::assertNotEquals( $string, $obfuscated );

        // less than 50% of the string should be similar
        similar_text( $string, $obfuscated, $percentage );
        self::assertTrue( $percentage <= 50 );
    }

    /**
     * @covers mc4wp_add_name_data
     */
    public function test_mc4wp_add_name_data() {
        foreach( $this->tests as $test ) {
            self::assertEquals( mc4wp_add_name_data( $test['input'] ), $test['output'] );
        }
    }

    /**
     * @covers mc4wp_guess_merge_vars
     */
    public function test_mc4wp_guess_merge_vars() {

       foreach( $this->tests as $test ) {
           self::assertEquals( mc4wp_guess_merge_vars( $test['input'] ), $test['output'] );
       }
    }
}