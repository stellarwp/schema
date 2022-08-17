<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Container;
use StellarWP\Schema\Fields;
use StellarWP\Schema\Register;
use StellarWP\Schema\Tables;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

class RegisterTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * Registered fields should exist in the collection
	 *
	 * @test
	 */
	public function it_should_have_fields_in_collection_when_added_individually() {
		$field_1 = $this->get_simple_table_field();

		Register::field( $field_1 );

		$this->assertArrayHasKey( $field_1::get_schema_slug(), Container::init()->make( Fields\Collection::class ) );
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

		$this->assertArrayHasKey( $field_1::get_schema_slug(), Container::init()->make( Fields\Collection::class ) );
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

		$this->assertArrayHasKey( $table_1::base_table_name(), Container::init()->make( Tables\Collection::class ) );
		$this->assertArrayHasKey( $table_2::base_table_name(), Container::init()->make( Tables\Collection::class ) );
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

		$this->assertArrayHasKey( $table_1::base_table_name(), Container::init()->make( Tables\Collection::class ) );
		$this->assertArrayHasKey( $table_2::base_table_name(), Container::init()->make( Tables\Collection::class ) );
	}

	/**
	 * Registered tables should be removed from the collection.
	 *
	 * @test
	 */
	public function it_should_remove_tables() {
		$table_1 = $this->get_simple_table();

		Register::table( $table_1 );

		$this->assertArrayHasKey( $table_1::base_table_name(), Container::init()->make( Tables\Collection::class ) );

		Register::remove_table( $table_1 );

		$this->assertArrayNotHasKey( $table_1::base_table_name(), Container::init()->make( Tables\Collection::class ) );
	}

	/**
	 * Registered fields should be removed from the collection.
	 *
	 * @test
	 */
	public function it_should_remove_fields() {
		$field_1 = $this->get_simple_table_field();

		Register::field( $field_1 );

		$this->assertArrayHasKey( $field_1::get_schema_slug(), Container::init()->make( Fields\Collection::class ) );

		Register::remove_field( $field_1 );

		$this->assertArrayNotHasKey( $field_1::get_schema_slug(), Container::init()->make( Fields\Collection::class ) );
	}
}