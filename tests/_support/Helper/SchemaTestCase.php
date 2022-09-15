<?php

namespace StellarWP\Schema\Tests;

use lucatume\DI52\Container;
use StellarWP\Schema\Config;
use StellarWP\DB\DB;
use StellarWP\Schema\Schema;

class SchemaTestCase extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		Config::set_container( new Container() );
		Config::set_db( DB::class );

		// Force the schema to be re-initialized.
		Config::get_container()->bind( 'stellarwp_schema_registered', static function() { return false; } );

		Schema::init();
	}
}
