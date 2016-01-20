<?php

class DebugLogReaderTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var string
	 */
	protected $file = '/tmp/mc4wp-debug.log';

	/**
	 * @var string
	 */
	protected $sample_log = array(
		'[2016-01-20 05:33:02] INFO: eCommerce360 > Successfully added order 101',
		'[2016-01-20 05:33:47] INFO: eCommerce360 > Order 101 deleted',
	);

	/**
	 * DebugLogReaderTest constructor.
	 */
	public function __construct() {
		parent::__construct();

		$contents = join( PHP_EOL, $this->sample_log ) . PHP_EOL;
		file_put_contents( $this->file, $contents );
	}

	/**
	 * @covers MC4WP_Debug_Log_Reader::all
	 */
	public function test_all() {
		$reader = new MC4WP_Debug_Log_Reader( $this->file );
		self::assertEquals( file_get_contents( $this->file ), $reader->all() );
	}

	/**
	 * @covers MC4WP_Debug_Log_Reader::read
	 */
	public function test_read() {
		$reader = new MC4WP_Debug_Log_Reader( $this->file );

		// first read should return first line
		self::assertEquals( $reader->read(), $this->sample_log[0] . PHP_EOL );

		// consecutive read should return second line
		self::assertEquals( $reader->read(), $this->sample_log[1] . PHP_EOL );
	}

	/**
	 * @covers MC4WP_Debug_Log_Reader::read_as_html
	 */
	public function test_read_as_html() {
		$reader = new MC4WP_Debug_Log_Reader( $this->file );

		$html = '<span class="time">[2016-01-20 05:33:02]</span> <span class="level">INFO:</span> <span class="message">eCommerce360 > Successfully added order 101</span>';
		self::assertEquals( $reader->read_as_html(), $html . PHP_EOL );
	}
}