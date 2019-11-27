<?php

/**
 * Class MC4WP_Debug_Log
 *
 * Simple logging class which writes to a file, loosely based on PSR-3.
 */
class MC4WP_Debug_Log {


	/**
	 * Detailed debug information
	 */
	const DEBUG = 100;

	/**
	 * Interesting events
	 *
	 * Examples: Visitor subscribed
	 */
	const INFO = 200;

	/**
	 * Exceptional occurrences that are not errors
	 *
	 * Examples: User already subscribed
	 */
	const WARNING = 300;

	/**
	 * Runtime errors
	 */
	const ERROR = 400;

	/**
	 * Logging levels from syslog protocol defined in RFC 5424
	 *
	 * @var array $levels Logging levels
	 */
	protected static $levels = array(
		self::DEBUG   => 'DEBUG',
		self::INFO    => 'INFO',
		self::WARNING => 'WARNING',
		self::ERROR   => 'ERROR',
	);

	/**
	 * @var string The file to which messages should be written.
	 */
	public $file;

	/**
	 * @var int Only write messages with this level or higher
	 */
	public $level;

	/**
	 * @var resource
	 */
	protected $stream;

	/**
	 * MC4WP_Debug_Log constructor.
	 *
	 * @param string $file
	 * @param mixed $level;
	 */
	public function __construct( $file, $level = self::DEBUG ) {
		$this->file  = $file;
		$this->level = self::to_level( $level );
	}

	/**
	 * @param mixed $level
	 * @param string $message
	 * @return boolean
	 */
	public function log( $level, $message ) {
		$level = self::to_level( $level );

		// only log if message level is higher than log level
		if ( $level < $this->level ) {
			return false;
		}

		// obfuscate email addresses in log message since log might be public.
		$message = mc4wp_obfuscate_email_addresses( (string) $message );

		// first, get rid of everything between "invisible" tags
		$message = preg_replace( '/<(?:style|script|head)>.+?<\/(?:style|script|head)>/is', '', $message );

		// then, strip tags (while retaining content of these tags)
		$message = strip_tags( $message );
		$message = trim( $message );

		// generate line
		$level_name = self::get_level_name( $level );
		$datetime   = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS ) );
		$message    = sprintf( '[%s] %s: %s', $datetime, $level_name, $message ) . PHP_EOL;

		// did we open stream yet?
		if ( ! is_resource( $this->stream ) ) {

			// open stream
			$this->stream = @fopen( $this->file, 'c+' );

			// if this failed, bail..
			if ( ! is_resource( $this->stream ) ) {
				return false;
			}

			// make sure first line of log file is a PHP tag + exit statement (to prevent direct file access)
			$line            = fgets( $this->stream );
			$php_exit_string = '<?php exit; ?>';
			if ( strpos( $line, $php_exit_string ) !== 0 ) {
				rewind( $this->stream );
				fwrite( $this->stream, $php_exit_string . PHP_EOL . $line );
			}

			// place pointer at end of file
			fseek( $this->stream, 0, SEEK_END );
		}

		// lock file while we write, ignore errors (not much we can do)
		flock( $this->stream, LOCK_EX );

		// write the message to the file
		fwrite( $this->stream, $message );

		// unlock file again, but don't close it for remainder of this request
		flock( $this->stream, LOCK_UN );

		$this->protect_log_file();

		return true;
	}

	/**
	 * @param string $message
	 * @return boolean
	 */
	public function warning( $message ) {
		return $this->log( self::WARNING, $message );
	}

	/**
	 * @param string $message
	 * @return boolean
	 */
	public function info( $message ) {
		return $this->log( self::INFO, $message );
	}

	/**
	 * @param string $message
	 * @return boolean
	 */
	public function error( $message ) {
		return $this->log( self::ERROR, $message );
	}

	/**
	 * @param string $message
	 * @return boolean
	 */
	public function debug( $message ) {
		return $this->log( self::DEBUG, $message );
	}

	/**
	 * Converts PSR-3 levels to local ones if necessary
	 *
	 * @param string|int Level number or name (PSR-3)
	 * @return int
	 */
	public static function to_level( $level ) {
		if ( is_string( $level ) ) {
			$level = strtoupper( $level );
			if ( defined( __CLASS__ . '::' . $level ) ) {
				return constant( __CLASS__ . '::' . $level );
			}

			throw new InvalidArgumentException( 'Level "' . $level . '" is not defined, use one of: ' . implode( ', ', array_keys( self::$levels ) ) );
		}

		return $level;
	}

	/**
	 * Gets the name of the logging level.
	 *
	 * @param  int    $level
	 * @return string
	 */
	public static function get_level_name( $level ) {
		if ( ! isset( self::$levels[ $level ] ) ) {
			throw new InvalidArgumentException( 'Level "' . $level . '" is not defined, use one of: ' . implode( ', ', array_keys( self::$levels ) ) );
		}

		return self::$levels[ $level ];
	}

	/**
	 * Tests if the log file is writable
	 *
	 * @return bool
	 */
	public function test() {
		$handle   = @fopen( $this->file, 'a' );
		$writable = false;

		if ( is_resource( $handle ) ) {
			$writable = true;
			fclose( $handle );
		}

		return $writable;
	}

	/**
	 * This writes a .htaccess file to the directory that the log file is in on servers supporting it.
	 */
	private function protect_log_file() {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) || substr( $_SERVER['SERVER_SOFTWARE'], 0, 6 ) !== 'Apache' ) {
			return;
		}

		$filename = basename( $this->file );
		$dirname = dirname( $this->file );
		$htaccess_file = $dirname . '/.htaccess';
		$lines = array(
			'# MC4WP Start',
			"<Files $filename>",
			'deny from all',
			'</Files>',
			'# MC4WP End',
		);

		if ( ! file_exists( $htaccess_file ) ) {
			file_put_contents( $htaccess_file, join( PHP_EOL, $lines ) );
			return;
		}

		$htaccess_content = file_get_contents( $htaccess_file );
		if ( strpos( $htaccess_content, $lines[0] ) === false ) {
			file_put_contents( $htaccess_file, PHP_EOL . PHP_EOL . join( PHP_EOL, $lines ), FILE_APPEND );
			return;
		}
	}
}
