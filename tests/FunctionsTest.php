<?php

/**
 * Class Functions_Test
 * @ignore
 */
class Functions_Test extends PHPUnit_Framework_TestCase {

    /**
     * @covers mc4wp_obfuscate_string
     */
    public function test_obfuscate_string() {

        // by no means should the two strings be similar
        $string = 'super-secret-string';
        $obfuscated = mc4wp_obfuscate_string( $string );
        self::assertNotEquals( $string, $obfuscated );

        // less than 50% of the string should be similar
        similar_text( $string, $obfuscated, $percentage );
        self::assertTrue( $percentage <= 50 );
    }

}