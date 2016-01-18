<?php

class MC4WP_Debug_Log{

	const DEBUG     = 100;
	const INFO      = 200;
	const WARNING   = 300;
	const ERROR     = 400;

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var int
	 */
	public $level;

	/**
	 * MC4WP_Debug_Log constructor.
	 *
	 * @param string $file
	 * @param int $level;
	 */
	public function __construct( $file, $level = self::DEBUG ) {
		$this->file = $file;
		$this->level = $level;
	}

	/**
	 * @param string $level
	 * @param string $message
	 */
	public function log( $level, $message ) {
		$message = sprintf( '[%s] %s: %s', date( 'Y-m-d H:i:s' ), $level, $message ) . PHP_EOL;
		$handle = fopen( $this->file, 'a+' );
		fwrite( $handle, $message );
		fclose( $handle );
	}

	/**
	 * @param $message
	 */
	public function warning( $message ) {
		if( self::WARNING >= $this->level ) {
			$this->log( 'WARNING', $message );
		}
	}

	/**
	 * @param $message
	 */
	public function info( $message ) {
		if( self::INFO >= $this->level ) {
			$this->log( 'INFO', $message );
		}
	}

	/**
	 * @param $message
	 */
	public function error( $message ) {
		if( self::ERROR >= $this->level ) {
			$this->log( 'ERROR', $message );
		}
	}

	/**
	 * @param       $message
	 */
	public function debug( $message ) {
		if( self::DEBUG >= $this->level ) {
			$this->log( 'DEBUG', $message );
		}
	}


}

