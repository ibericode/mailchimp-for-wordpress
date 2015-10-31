<?php

class MC4WP_Plugin {

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @param string $file
	 * @param string $version
	 */
	public function __construct( $file, $version ) {
		$this->file = $file;
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function file() {
		return $this->file;
	}

	/**
	 * @return string
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function dir( $path = '' ) {
		return dirname( $this->file ) . $path;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function url( $path = '' ) {
		return plugins_url( $path, $this->file );
	}
}