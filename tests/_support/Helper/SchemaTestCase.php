<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Schema;

class SchemaTestCase extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		// before
		parent::setUp();

		Schema::init();
	}
}
