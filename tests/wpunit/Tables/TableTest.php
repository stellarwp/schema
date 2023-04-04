<?php

namespace StellarWP\Schema\Tests\Tables;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

class TableTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_simple_table()->drop();
		$this->get_foreign_key_table()->drop();
	}

	/**
	 * Should have index.
	 *
	 * @test
	 */
	public function should_have_index() {
		$table = $this->get_simple_table();

		Register::table( $table );

		$this->assertTrue( $table->has_index( 'slug' ) );
	}

	/**
	 * Should not have fake index.
	 *
	 * @test
	 */
	public function should_have_fake_index() {
		$table = $this->get_simple_table();

		Register::table( $table );

		$this->assertFalse( $table->has_index( 'bork' ) );
	}

	/**
	 * It should not have foreign key
	 *
	 * @test
	 */
	public function should_not_have_foreign_key() {
		$simple_table      = $this->get_simple_table();
		$foreign_key_table = $this->get_foreign_key_table();

		Register::table( $simple_table );
		Register::table( $foreign_key_table );

		$this->assertFalse( $simple_table->has_foreign_key( 'simple_id' ) );
		$this->assertFalse( $foreign_key_table->has_foreign_key( 'not_really' ) );
	}

	/**
	 * It should have foreign key
	 *
	 * @test
	 */
	public function should_have_foreign_key() {
		$simple_table      = $this->get_simple_table();
		$foreign_key_table = $this->get_foreign_key_table();

		Register::table( $simple_table );
		Register::table( $foreign_key_table );

		$this->assertFalse( $simple_table->has_foreign_key( 'simple_id' ) );
		$this->assertFalse( $foreign_key_table->has_foreign_key( 'not_really' ) );

		$this->assertTrue( $foreign_key_table->has_foreign_key( 'simple_id' ) );
	}
}

