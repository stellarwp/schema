<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Container;
use StellarWP\Schema\Schema;

class SchemaTestCase extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		$container = Container::init();

		// Force the schema to be re-initialized.
		$container->setVar( 'stellarwp_schema_registered', false );

		Schema::init();
	}
}
