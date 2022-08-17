<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Schema;

class SchemaTestCase extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		Schema::init();
	}
}
