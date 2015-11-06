<?php

/**
 * Class MC4WP_DB_Upgrader
 *
 * This class takes care of loading migration files from `./migrations/`.
 * Migration files should only use default WP functions and 0 code which might not be there in the future.
 *
 * @ignore
 */
class MC4WP_Upgrade_Routines {

	/**
	 * @var float
	 */
	protected $version_from = 0;

	/**
	 * @var float
	 */
	protected $version_to = 0;

	/**
	 * @param float $from
	 * @param float $to
	 */
	public function __construct( $from, $to ) {
		$this->version_from = $from;
		$this->version_to = $to;
	}

	/**
	 * Run the various upgrade routines, all the way up to the latest version
	 */
	public function run() {
		define( 'MC4WP_DOING_UPGRADE', true );

		$migrations = $this->find_migrations();

		// run in sub-function for scope
		array_map( array( $this, 'run_migration' ), $migrations );

		// update code version
		update_option( 'mc4wp_version', MC4WP_VERSION );
	}

	/**
	 * @return array
	 */
	protected function find_migrations() {

		$files = glob( dirname( __FILE__ ) . '/migrations/*.php' );
		$migrations =  array();


		foreach( $files as $file ) {
			$migration = basename( $file );
			$parts = explode( '-', $migration );
			$version = $parts[0];

			if( version_compare( $this->version_from, $version, '<' ) ) {
				$migrations[] = $file;
			}
		}

		return $migrations;
	}

	/**
	 * @param string $file
	 */
	protected function run_migration( $file ) {
		include $file;
	}







}