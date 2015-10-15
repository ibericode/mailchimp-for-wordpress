<?php

class FormValidatorTest extends PHPUnit_Framework_TestCase {

	public function __construct() {
		$this->validator = new MC4WP_Form_Validator( array() );
	}

	/**
	 * @covers MC4WP_Form_Validator::validate_nonce
	 */
	public function test_validate_nonce() {

		// always true when using caching
		if( ! defined( 'WP_CACHE' ) ) {
			define( 'WP_CACHE', true );
		}

		$this->assertTrue( $this->validator->validate_nonce() );
	}

	/**
	 * @covers MC4WP_Form_Validator::validate_honeypot
	 */
	public function test_validate_honeypot() {
		// no honeypot submitted
		$this->validator->set_data( array() );
		$this->assertFalse( $this->validator->validate_honeypot() );

		// honeypot filled
		$this->validator->set_data( array( 'honeypot' => 'some string' ) );
		$this->assertFalse( $this->validator->validate_honeypot() );

		// honeypot submitted but not filled
		$this->validator->set_data( array( 'honeypot' => '' ) );
		$this->assertTrue( $this->validator->validate_honeypot() );
	}

	/**
	 * @covers MC4WP_Form_Validator::validate_timestamp
	 */
	public function test_validate_timestamp() {
		// no timestamp given
		$this->validator->set_data( array() );
		$this->assertFalse( $this->validator->validate_timestamp() );

		// timestamp in future
		$this->validator->set_data( array( 'timestamp' => time() + 10 ) );
		$this->assertFalse( $this->validator->validate_timestamp() );

		// timestamp just 1 second ago
		$this->validator->set_data( array( 'timestamp' => time() - 1 ) );
		$this->assertFalse( $this->validator->validate_timestamp() );

		// timestamp more than 2 seconds ago
		$this->validator->set_data( array( 'timestamp' => time() - 2 ) );
		$this->assertTrue( $this->validator->validate_timestamp() );
	}

	/**
	 * @covers MC4WP_Form_Validator::validate_captcha
	 */
	public function test_validate_captcha() {
		$this->validator->set_data( array() );
		$this->assertTrue( $this->validator->validate_captcha() );

		$this->validator->set_data( array( 'has_captcha' => 1 ) );
		$this->assertTrue( $this->validator->validate_captcha() );
	}

	/**
	 * @covers MC4WP_Form_Validator::validate_email
	 */
	public function test_validate_email() {
		$this->validator->set_data( array() );
		$this->assertFalse( $this->validator->validate_email() );

		$this->validator->set_data( array(), array( 'EMAIL' => array() ) );
		$this->assertFalse( $this->validator->validate_email() );

//		$this->validator->set_data( array( 'EMAIL' => 'invalid@email') );
//		$this->assertFalse( $this->validator->validate_email() );
//
//		$this->validator->set_data( array( 'EMAIL' => 'support@mc4wp.com' ) );
//		$this->assertTrue( $this->validator->validate_email() );
	}

	/**
	 * @covers MC4WP_Form_Validator::validate_lists
	 */
	public function test_validate_lists() {
		$this->assertFalse( $this->validator->validate_lists( array() ) );
		$this->assertTrue( $this->validator->validate_lists( array( 'list-id-1') ) );
	}

}