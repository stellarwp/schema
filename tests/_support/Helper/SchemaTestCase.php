<?php

namespace StellarWP\Schema\Tests;

use lucatume\DI52\App;
use StellarWP\Schema\Schema;

class SchemaTestCase extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		$container = App::container();

		// Force the schema to be re-initialized.
		$container->setVar( 'stellarwp_schema_registered', false );

		Schema::init();
	}
}
