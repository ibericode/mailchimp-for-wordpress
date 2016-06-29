<?php

/**
 * Class MC4WP_DB_Upgrader
 *
 * This class takes care of loading migration files from the specified migrations directory.
 * Migration files should only use default WP functions and NOT use code which might not be there in the future.
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
	 * @var string
	 */
	protected $migrations_dir = '';

	/**
	 * @param float $from
	 * @param float $to
	 */
	public function __construct( $from, $to, $migrations_dir ) {
		$this->version_from = $from;
		$this->version_to = $to;
		$this->migrations_dir = $migrations_dir;
	}

	/**
	 * Run the various upgrade routines, all the way up to the latest version
	 */
	public function run() {
		$migrations = $this->find_migrations();

		// run in sub-function for scope
		array_map( array( $this, 'run_migration' ), $migrations );
	}

	/**
	 * @return array
	 */
	public function find_migrations() {

		$files = glob( rtrim( $this->migrations_dir, '/' ) . '/*.php' );
		$migrations =  array();

		// return empty array when glob returns non-array value.
		if( ! is_array( $files ) ) {
			return $migrations;
		}

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
	 * Include a migration file and runs it.
	 *
	 * @param string $file
	 */
	protected function run_migration( $file ) {
		include $file;
	}







}