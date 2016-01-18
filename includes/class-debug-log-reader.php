<?php


class MC4WP_Debug_Log_Reader {

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
	 * Reads X number of lines.
	 *
	 * If $start is negative, reads from end of log file.
	 *
	 * @param $start
	 * @param $number
	 */
	public function lines( $start, $number ) {
			// TODO
	}


}