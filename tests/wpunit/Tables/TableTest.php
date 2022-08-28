<?php

namespace StellarWP\Schema\Tests\Tables;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

class TableTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * Should have index.
	 *
	 * @test
	 */
	public function should_have_index() {
		$table   = $this->get_simple_table();

		Register::table( $table );

		$this->assertTrue( $table->has_index( 'slug' ) );
	}

	/**
	 * Should not have fake index.
	 *
	 * @test
	 */
	public function should_have_fake_index() {
		$table   = $this->get_simple_table();

		Register::table( $table );

		$this->assertFalse( $table->has_index( 'bork' ) );
	}
}

