<?php

namespace StellarWP\Schema\Tests\Traits;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;
use StellarWP\Schema\Columns\Integer_Column;
use StellarWP\Schema\Columns\String_Column;
use StellarWP\Schema\Columns\ID;
use StellarWP\Schema\Columns\Column_Types;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Tables\Contracts\Table;
use StellarWP\Schema\Tables\Table_Schema;
use StellarWP\DB\DB;

class Custom_Table_Query_MethodsTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * @before
	 * @after
	 */
	public function drop_tables() {
		$this->get_query_test_table()->drop();
	}

	/**
	 * @test
	 */
	public function should_update_multiple_with_array_values() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [
			'name' => 'Test 1',
			'slug' => 'test-1',
			'status' => 1,
		] );

		$id1 = DB::last_insert_id();

		$table::insert( [
			'name' => 'Test 2',
			'slug' => 'test-2',
			'status' => 1,
		] );

		$id2 = DB::last_insert_id();

		// Update multiple rows using array values
		$updated = $table::update_many( [
			[
				'id' => $id1,
				'name' => 'Updated Test 1',
			],
			[
				'id' => $id2,
				'name' => 'Updated Test 2',
			],
		] );

		$this->assertEquals( 2, $updated );

		// Verify the updates
		$result1 = $table::get_first_by( 'slug', 'test-1' );
		$this->assertEquals( 'Updated Test 1', $result1['name'] );

		$result2 = $table::get_first_by( 'slug', 'test-2' );
		$this->assertEquals( 'Updated Test 2', $result2['name'] );
	}

	/**
	 * @test
	 */
	public function should_get_all_by_with_array_values() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [
			'name' => 'Test 1',
			'slug' => 'test-1',
			'status' => 1,
		] );

		$id1 = DB::last_insert_id();

		$table::insert( [
			'name' => 'Test 2',
			'slug' => 'test-2',
			'status' => 1,
		] );

		$id2 = DB::last_insert_id();

		$table::insert( [
			'name' => 'Test 3',
			'slug' => 'test-3',
			'status' => 0,
		] );

		$id3 = DB::last_insert_id();

		// Get all by status using array (simulating IN operator scenario)
		$results = $table::get_all_by( 'status', [ 1, 0 ], 'IN' );

		$this->assertCount( 3, $results );

		$this->assertEquals( 'Test 1', $results[0]['name'] );
		$this->assertEquals( 'Test 2', $results[1]['name'] );
		$this->assertEquals( 'Test 3', $results[2]['name'] );

		$this->assertEquals( 1, $results[0]['status'] );
		$this->assertEquals( 1, $results[1]['status'] );
		$this->assertEquals( 0, $results[2]['status'] );
	}

	/**
	 * @test
	 */
	public function should_get_first_by_with_array_values() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [
			'name' => 'First Match',
			'slug' => 'first-match',
			'status' => 1,
		] );

		$table::insert( [
			'name' => 'Second Match',
			'slug' => 'second-match',
			'status' => 1,
		] );

		// Get first by slug
		$result = $table::get_first_by( 'slug', [ 'second-match' ], 'NOT IN' );

		$this->assertNotNull( $result );
		$this->assertEquals( 'First Match', $result['name'] );
	}

	/**
	 * @test
	 */
	public function should_update_multiple_with_integer_array_values() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [
			'name' => 'Active Item',
			'slug' => 'active-item',
			'status' => 1,
		] );

		$id1 = DB::last_insert_id();

		// Update using integer value
		$updated = $table::update_many( [
			[
				'id' => $id1,
				'status' => 0,
			],
		] );

		$this->assertEquals( 1, $updated );

		// Verify the update
		$result = $table::get_first_by( 'slug', 'active-item' );
		$this->assertEquals( 0, $result['status'] );
	}

	/**
	 * @test
	 */
	public function should_handle_scalar_values_in_queries() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data with scalar values
		$table::insert( [
			'name' => 'Scalar Test',
			'slug' => 'scalar-test',
			'status' => 1,
		] );

		$id = DB::last_insert_id();

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		// Verify scalar value retrieval
		$result = $table::get_first_by( 'id', $id );
		$this->assertEquals( 'Scalar Test', $result['name'] );
	}

	/**
	 * Get a test table for query method testing.
	 */
	private function get_query_test_table() {
		return new class extends Table {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'query_test';
			protected static $group = 'test';
			protected static $schema_slug = 'test-query';

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					$columns[] = ( new ID( 'id' ) )->set_length( 11 )->set_type( Column_Types::INT );
					$columns[] = ( new String_Column( 'name' ) )->set_length( 255 );
					$columns[] = ( new String_Column( 'slug' ) )->set_length( 255 )->set_is_index( true );
					$columns[] = ( new Integer_Column( 'status' ) )->set_length( 1 )->set_default( 0 );

					return new Table_Schema( $table_name, $columns );
				};

				return [
					static::SCHEMA_VERSION => $callable,
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
	}
}
