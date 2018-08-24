<?php

use PHPUnit\Framework\TestCase;

/**
 * Class ContainerTest
 *
 * @ignore
 */
class ContainerTest extends TestCase {

	/**
	 * @covers PL4WP_Container::get
	 */
	public function test_get() {
		$container = new PL4WP_Container();

		$this->expectException('Exception');
		$container->get( 'foo' );
		$this->expectException('');

		$container['foo'] = 'bar';
		self::assertEquals( $container->get('foo'), 'bar');

		// overwrite
		$container['foo'] = 'new bar';
		self::assertEquals( $container->get('foo'), 'new bar');
	}

	/**
	 * @covers PL4WP_Container::has
	 */
	public function test_has() {
		$container = new PL4WP_Container();

		self::assertFalse( $container->has('foo') );

		$container['foo'] = 'bar';
		self::assertTrue( $container->has( 'foo' ) );
		self::assertfalse( $container->has('foo2' ) );
	}

	public function test_resolving_service() {
		// assert services are resolved
		$container = new PL4WP_Container();
		$container['service'] = function() {
			return 'resolved';
		};

		self::assertEquals($container->get('service'), 'resolved');

		// assert resolved services return the same instance
		$container['instance'] = function() {
			$instance = new StdClass();
			$instance->prop = 'value';
			return $instance;
		};
		$one = $container['instance'];
		$two = $container['instance'];
		self::assertTrue( $one === $two );
	}

}
