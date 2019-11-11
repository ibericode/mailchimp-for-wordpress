<?php

/**
 * Class MC4WP_Plugin
 *
 * Helper class for easy access to information like the plugin file or plugin directory.
 * Used in MC4WP Premium.
 *
 * @access public
 * @ignore
 */
class MC4WP_Plugin {


	/**
	 * @var string The plugin version.
	 */
	protected $version;

	/**
	 * @var string The main plugin file.
	 */
	protected $file;

	/**
	 * @param string $file The plugin version.
	 * @param string $version The main plugin file.
	 */
	public function __construct( $file, $version ) {
		$this->file    = $file;
		$this->version = $version;
	}

	/**
	 * Get the main plugin file.
	 *
	 * @return string
	 */
	public function file() {
		return $this->file;
	}

	/**
	 * Get the plugin version.
	 *
	 * @return string
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Gets the directory the plugin lives in.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function dir( $path = '' ) {

		// ensure path has leading slash
		if ( '' !== $path ) {
			$path = '/' . ltrim( $path, '/' );
		}

		return dirname( $this->file ) . $path;
	}

	/**
	 * Gets the URL to the plugin files.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function url( $path = '' ) {
		return plugins_url( $path, $this->file );
	}
}
