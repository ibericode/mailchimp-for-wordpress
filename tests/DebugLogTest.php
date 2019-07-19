<?php

use PHPUnit\Framework\TestCase;

class DebugLogTest extends TestCase
{

    /**
     * @var string
     */
    private $file = '/tmp/mc4wp-debug.log';

    /**
     * @covers MC4WP_Debug_Log::log
     */
    public function test_log()
    {
        $logger = new MC4WP_Debug_Log($this->file, 0);

        // test writing to file
        $message = "Sample log message";
        self::assertTrue($logger->log(200, $message));
        self::assertStringEndsWith($message . PHP_EOL, file_get_contents($this->file));

        // "something" is an invalid log level, should throw exception
        self::expectException('InvalidArgumentException');
        $logger->log('something', 'Message');
    }

    /**
     * @covers MC4WP_Debug_Log::debug
     */
    public function test_debug()
    {
        $message = "Sample debug message";

        // level is 200, debug is 100 so this shouldn't log
        $logger = new MC4WP_Debug_Log($this->file, 200);
        self::assertFalse($logger->debug($message));

        // level is 100, debug is 100 so this should log
        $logger = new MC4WP_Debug_Log($this->file, 100);
        self::assertTrue($logger->debug($message));

        // test string
        $logger = new MC4WP_Debug_Log($this->file, 'debug');
        self::assertTrue($logger->debug($message));

        // test string
        $logger = new MC4WP_Debug_Log($this->file, 'warning');
        self::assertFalse($logger->debug($message));
    }

    /**
     * @covers MC4WP_Debug_Log::info
     */
    public function test_info()
    {
        $message = "Sample info message";

        // this shouldn't log (info level is 200)
        $logger = new MC4WP_Debug_Log($this->file, 300);
        self::assertFalse($logger->info($message));

        // this should
        $logger = new MC4WP_Debug_Log($this->file, 200);
        self::assertTrue($logger->info($message));

        // test string
        $logger = new MC4WP_Debug_Log($this->file, 'info');
        self::assertTrue($logger->info($message));

        // test string with higher level
        $logger = new MC4WP_Debug_Log($this->file, 'warning');
        self::assertFalse($logger->info($message));
    }

    /**
     * @covers MC4WP_Debug_Log::warning
     */
    public function test_warning()
    {
        $message = "Sample warning message";

        // this shouldn't log (warning level is 300)
        $logger = new MC4WP_Debug_Log($this->file, 400);
        self::assertFalse($logger->warning($message));

        // this should
        $logger = new MC4WP_Debug_Log($this->file, 300);
        self::assertTrue($logger->warning($message));

        // test string
        $logger = new MC4WP_Debug_Log($this->file, 'warning');
        self::assertTrue($logger->warning($message));

        // test string with higher level
        $logger = new MC4WP_Debug_Log($this->file, 'error');
        self::assertFalse($logger->warning($message));
    }


    /**
     * @covers MC4WP_Debug_Log::error
     */
    public function test_error()
    {
        $message = "Sample error message";

        // this should log
        $logger = new MC4WP_Debug_Log($this->file, 200);
        self::assertTrue($logger->error($message));

        // test string
        $logger = new MC4WP_Debug_Log($this->file, 'error');
        self::assertTrue($logger->error($message));
    }



    /**
     * Remove log files after each test.
     */
    public function tearDown() : void
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }
}
