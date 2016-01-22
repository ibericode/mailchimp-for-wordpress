<?php

class QueueJobTest extends PHPUnit_Framework_TestCase {


	public function test_constructor() {
		$data = array( 'sample' => 'data' );
		$instance = new MC4WP_Queue_Job( $data );
		self::assertEquals( $instance->data, $data );
		self::assertNotEmpty( $instance->id );
	}


}