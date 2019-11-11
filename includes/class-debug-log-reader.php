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
	private static $regex = '/^(\[[\d \-\:]+\]) (\w+\:) (.*)$/S';

	/**
	 * @var string
	 */
	private static $html_template = '<span class="time">$1</span> <span class="level">$2</span> <span class="message">$3</span>';

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
	 * Sets file pointer to $n of lines from the end of file.
	 *
	 * @param int $n
	 */
	private function seek_line_from_end( $n ) {
		$line_count = 0;

		// get line count
		while ( ! feof( $this->handle ) ) {
			fgets( $this->handle );
			$line_count++;
		}

		// rewind to beginning
		rewind( $this->handle );

		// calculate target
		$target  = $line_count - $n;
		$target  = $target > 1 ? $target : 1; // always skip first line because oh PHP header
		$current = 0;

		// keep reading until we're at target
		while ( $current < $target ) {
			fgets( $this->handle );
			$current++;
		}
	}

	/**
	 * @return string|null
	 */
	public function read() {

		// open file if not yet opened
		if ( ! is_resource( $this->handle ) ) {

			// doesn't exist?
			if ( ! file_exists( $this->file ) ) {
				return null;
			}

			$this->handle = @fopen( $this->file, 'r' );

			// unable to read?
			if ( ! is_resource( $this->handle ) ) {
				return null;
			}

			// set pointer to 1000 files from EOF
			$this->seek_line_from_end( 1000 );
		}

		// stop reading once we're at the end
		if ( feof( $this->handle ) ) {
			fclose( $this->handle );
			$this->handle = null;
			return null;
		}

		// read line, up to 8kb
		$text = fgets( $this->handle );

		// strip tags & trim
		$text = strip_tags( $text );
		$text = trim( $text );

		return $text;
	}

	/**
	 * @return string
	 */
	public function read_as_html() {
		$line = $this->read();

		// null means end of file
		if ( is_null( $line ) ) {
			return null;
		}

		// empty string means empty line, but not yet eof
		if ( empty( $line ) ) {
			return '';
		}

		$line = preg_replace( self::$regex, self::$html_template, $line );
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
		$lines  = '';

		$current_line = 0;
		while ( $current_line < $number ) {
			$lines .= fgets( $handle );
		}

		fclose( $handle );
		return $lines;
	}
}
