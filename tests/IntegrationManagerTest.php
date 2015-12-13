<?php

/**
 * Class IntegrationManagerTest
 *
 * @ignore
 */
class IntegrationManagerTest extends PHPUnit_Framework_TestCase {

	public function test_constructor() {
		$instance = new MC4WP_Integration_Manager();
		$property = PHPUnit_Framework_Assert::readAttribute( $instance, 'tags' );
		self::assertInstanceOf( 'MC4WP_Integration_Tags', $property );
	}

	/**
	 * @covers MC4WP_Integration_Manager::register_integration
	 * @covers MC4WP_Integration_Manager::get_enabled_integrations
	 * @covers MC4WP_Integration_Manager::get_all
	 */
	public function test_register_integration() {
		$instance = new MC4WP_Integration_Manager();
		$instance->register_integration( 'slug', 'MC4WP_Sample_Integration', false );

		self::assertNotEmpty( $instance->get_all() );
		self::assertEmpty( $instance->get_enabled_integrations() );

		$instance->register_integration( 'another-slug', 'MC4WP_Sample_Integration', true );
		self::assertNotEmpty( $instance->get_enabled_integrations() );

		// if we register same slug twice, former should be overwritten so count should still be 2 here
		$instance->register_integration( 'slug', 'MC4WP_Sample_Integration', false );
		self::assertCount( 2, $instance->get_all() );
	}

	/**
	 * @covers MC4WP_Integration_Manager::deregister_integration
	 * @covers MC4WP_Integration_Manager::get_enabled_integrations
	 * @covers MC4WP_Integration_Manager::get_all
	 */
	public function test_deregister_integration() {
		$instance = new MC4WP_Integration_Manager();
		$instance->register_integration( 'slug', 'ClassName', true );
		$instance->deregister_integration('slug');

		self::assertEmpty( $instance->get_all() );
		self::assertEmpty( $instance->get_enabled_integrations() );
	}

	/**
	 * @covers MC4WP_Integration_Manager::get
	 */
	public function test_get() {
		$instance = new MC4WP_Integration_Manager();
		self::setExpectedException( 'Exception' );
		$instance->get('non-existing-slug');


		$instance->register_integration( 'slug', 'MC4WP_Sample_Integration', true );
		self::setExpectedException(null);
		self::assertInstanceOf( 'MC4WP_Sample_Integration', $instance->get('slug') );
	}




}

class MC4WP_Sample_Integration extends MC4WP_Integration {
	public function add_hooks() {}
	public function is_installed() {
		return true;
	}
}