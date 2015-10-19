<?php

/**
 * Class MC4WP_Remote_Content_Block_Mock
 */
class MC4WP_Remote_Content_Block_Mock extends MC4WP_Remote_Content_Block {
	public $cache_key;
	public $content;
}

// mock sanitize_key function
function sanitize_key( $string ) {
	return $string;
}


/**
* Class RemoteContentBlockTest
 */
class RemoteContentBlockTest extends PHPUnit_Framework_TestCase {

	/**
	 * Cache key can be no longer than 45 characters (transient key limit)
	 */
	public function testCacheKeyLength() {
		$instance = new MC4WP_Remote_Content_Block_Mock( str_repeat( 'i', 200 ) );
		$this->assertLessThan( 45, strlen( $instance->cache_key ) );
	}

}