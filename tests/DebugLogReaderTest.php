<?php

use PHPUnit\Framework\TestCase;

class DebugLogReaderTest extends TestCase {

	/**
	 * @var string
	 */
	protected $file = '/tmp/pl4wp-debug.log';

	/**
	 * @var string
	 */
	protected $sample_log_lines = array(
		'eCommerce360 > Successfully added order 101',
		'eCommerce360 > Order 101 deleted',
	);

	/**
	 * DebugLogReaderTest constructor.
	 */
	public function __construct() {
		parent::__construct();

        $log = new PL4WP_Debug_Log( $this->file );
        foreach( $this->sample_log_lines as $line ) {
            $log->error( $line );
        }

	}

	/**
	 * @covers PL4WP_Debug_Log_Reader::all
	 */
	public function test_all() {
		$reader = new PL4WP_Debug_Log_Reader( $this->file );
		self::assertEquals( file_get_contents( $this->file ), $reader->all() );
	}

	/**
	 * @covers PL4WP_Debug_Log_Reader::read
	 */
	public function test_read() {
		$reader = new PL4WP_Debug_Log_Reader( $this->file );

		// first read should return first line
		self::assertContains( $this->sample_log_lines[0], $reader->read() );

        // read should match format
        self::assertRegExp('/^\[([0-9-: ]+)\] (INFO|WARNING|ERROR)\: (.*)$/', $reader->read() );

	}

	/**
	 * @covers PL4WP_Debug_Log_Reader::read_as_html
	 */
	public function test_read_as_html() {
		$reader = new PL4WP_Debug_Log_Reader( $this->file );
        self::assertRegExp('/^<span .*>\[([0-9-: ]+)\]<\/span> <span .*>(INFO|WARNING|ERROR)\:<\/span> <span .*>(.*)<\/span>\n$/', $reader->read_as_html() );
	}
}
