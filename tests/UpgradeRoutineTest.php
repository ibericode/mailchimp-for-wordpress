<?php

/**
 * Class UpgradeRoutineTest
 *
 * @ignore
 */
class UpgradeRoutineTest extends PHPUnit_Framework_TestCase {

	private $dir = '/tmp/mc4wp-tests/migrations';

	/**
	 * Create the sample migrations directory
	 */
	public function setUp() {
		if( ! file_exists( $this->dir ) ) {
			mkdir( $this->dir, 0700, true );
		}
	}

	/**
	 * @covers MC4WP_Upgrade_Routines::find_migrations
	 */
	public function test_find_migrations() {
		$instance = new MC4WP_Upgrade_Routines( '1.0', '1.1', $this->dir );
		self::assertEquals( $instance->find_migrations(), array() );

		// create correct migration file
		$migration_file =  $this->dir . '/1.1-do-something.php';
		file_put_contents( $migration_file, '' );
		self::assertEquals( $instance->find_migrations(), array( $migration_file ) );

		// create incorrect migrations file
		$older_migration_file =  $this->dir . '/1.0-do-something.php';
		file_put_contents( $older_migration_file, '' );
		self::assertEquals( $instance->find_migrations(), array( $migration_file ) );
	}

	/**
	 * Remove files after each test.
	 */
	public function tearDown() {
		array_map( 'unlink', glob( $this->dir . '/*.php' ) );
		if( file_exists( $this->dir ) ) {
			rmdir( $this->dir );
		}
	}


}