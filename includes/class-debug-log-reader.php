<?php

/**
 * Class MC4WP_Debug_Log_Reader
 */
class MC4WP_Debug_Log_Reader {

	/**
	 * @var resource|null
	 */
	private $handle;

	/**
	 * @var string
	 */
	private static $regex = '/^(\[[\d \-\:]+\]) (\w+\:) (.*)$/';

	/**
	 * @var string The log file location.
	 */
	private $file;

	/**
	 * MC4WP_Debug_Log_Reader constructor.
	 *
	 * @param $file
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * @return string
	 */
	public function all() {
		return file_get_contents( $this->file );
	}

	/**
	 * @return string
	 */
	public function read() {

		// open file if not yet opened
		if( ! is_resource( $this->handle ) ) {

			// doesn't exist?
			if( ! file_exists( $this->file ) ) {
				return '';
			}

			$this->handle = fopen( $this->file, 'r' );
		}

		// read line, up to 8kb
		$text = fgets( $this->handle );

		// close file as soon as we reach an empty line
		if( empty( $text ) ) {
			fclose( $this->handle );
			$this->handle = null;
			return '';
		}

		return $text;
	}

	/**
	 * @return string
	 */
	public function read_as_html() {

		$line = $this->read();

		if( empty( $line ) ) {
			return '';
		}

		$line = preg_replace( self::$regex, '<span class="time">$1</span> <span class="level">$2</span> <span class="message">$3</span>', $line );

		return $line;
	}

	/**
	 * Reads X number of lines.
	 *
	 * If $start is negative, reads from end of log file.
	 *
	 * @param int $start
	 * @param int $number
	 * @return string
	 */
	public function lines( $start, $number ) {
		$handle = fopen( $start, 'r' );
		$lines = '';

		$current_line = 0;
		while( $current_line < $number ) {
			$lines .= fgets( $handle );
		}

		fclose( $handle );
		return $lines;
	}

}