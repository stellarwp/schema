<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Config;
use StellarWP\Schema\Fields;
use StellarWP\Schema\Register;
use StellarWP\Schema\Schema;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

class RegisterTest extends SchemaTestCase {

	use Table_Fixtures;

	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_simple_table()->drop();
		$this->get_simple_table_alt_group()->drop();
		$this->get_foreign_key_table()->drop();
	}

	/**
	 * Registered fields should exist in the collection
	 *
	 * @test
	 */
	public function it_should_have_fields_in_collection_when_added_individually() {
		$field_1 = $this->get_simple_table_field();

		Register::field( $field_1 );

		$this->assertArrayHasKey( $field_1::get_schema_slug(), Config::get_container()->get( Fields\Collection::class ) );
	}

	/**
	 * Batch registered tables should exist in the collection
	 *
	 * @test
	 */
	public function it_should_have_fields_in_collection_when_batch_added() {
		$field_1 = $this->get_simple_table_field();

		Register::fields( [
			$field_1,
		]);

		$this->assertArrayHasKey( $field_1::get_schema_slug(), Config::get_container()->get( Fields\Collection::class ) );
	}

	/**
	 * Registered tables should exist in the collection
	 *
	 * @test
	 */
	public function it_should_have_tables_in_collection_when_added_individually() {
		$table_1 = $this->get_simple_table();
		$table_2 = $this->get_indexless_table();

		Register::table( $table_1 );
		Register::table( $table_2 );

		$this->assertArrayHasKey( $table_1::base_table_name(), Schema::tables() );
		$this->assertArrayHasKey( $table_2::base_table_name(), Schema::tables() );
	}

	/**
	 * Batch registered tables should exist in the collection
	 *
	 * @test
	 */
	public function it_should_have_tables_in_collection_when_batch_added() {
		$table_1 = $this->get_simple_table();
		$table_2 = $this->get_indexless_table();

		Register::tables( [
			$table_1,
			$table_2,
		] );

		$this->assertArrayHasKey( $table_1::base_table_name(), Schema::tables() );
		$this->assertArrayHasKey( $table_2::base_table_name(), Schema::tables() );
	}

	/**
	 * @test
	 */
	public function it_should_allow_fetching_tables_by_single_group() {
		Register::tables( [
			$this->get_simple_table(),
			$this->get_simple_table_alt_group(),
			$this->get_indexless_table(),
		] );

		$tables = Schema::tables()->get_by_group( 'bork' );

		$this->assertSame( 2, $tables->count() );
	}

	/**
	 * @test
	 */
	public function it_should_allow_fetching_tables_by_multiple_groups() {
		Register::tables( [
			$this->get_simple_table(),
			$this->get_simple_table_alt_group(),
			$this->get_indexless_table(),
		] );

		$tables = Schema::tables()->get_by_group( [ 'bork', 'test' ] );

		$this->assertSame( 3, $tables->count() );
	}

	/**
	 * @test
	 */
	public function it_should_allow_fetching_of_tables_that_need_updates() {
		Register::tables([
			$this->get_simple_table(),
			$this->get_indexless_table(),
		]);

		$schema_tables = Schema::tables();

		$tables = $schema_tables->get_tables_needing_updates();

		$this->assertSame( 0, $tables->count() );

		$schema_tables->add( $this->get_modified_simple_table() );

		$this->assertSame( 1, $tables->count() );
	}

	/**
	 * Registered tables should be removed from the collection.
	 *
	 * @test
	 */
	public function it_should_remove_tables() {
		$table_1 = $this->get_simple_table();

		Register::table( $table_1 );

		$this->assertArrayHasKey( $table_1::base_table_name(), Schema::tables() );

		Register::remove_table( $table_1 );

		$this->assertArrayNotHasKey( $table_1::base_table_name(), Schema::tables() );
	}

	/**
	 * Registered fields should be removed from the collection.
	 *
	 * @test
	 */
	public function it_should_remove_fields() {
		$field_1 = $this->get_simple_table_field();

		Register::field( $field_1 );

		$this->assertArrayHasKey( $field_1::get_schema_slug(), Schema::fields() );

		Register::remove_field( $field_1 );

		$this->assertArrayNotHasKey( $field_1::get_schema_slug(), Schema::fields() );
	}
}
