<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Container;

class ContainerTest extends SchemaTestCase {
	/**
	 * Test that the container is correctly instantiated.
	 *
	 * @test
	 */
	public function it_should_instantiate() {
		$container = Container::init();

		$this->assertInstanceOf( Container::class, $container );
	}
}
