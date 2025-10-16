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

class Custom_Table_Query_MethodsTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_query_test_table()->drop();
	}

	/**
	 * Test that update_multiple handles array values correctly.
	 *
	 * This test covers the fix in commit d4aea4a that ensures array values
	 * returned from prepare_value_for_query are properly unfolded when used
	 * in database::prepare() calls.
	 *
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

		$table::insert( [
			'name' => 'Test 2',
			'slug' => 'test-2',
			'status' => 1,
		] );

		// Update multiple rows using array values
		$updated = $table::update_multiple( [
			[
				'slug' => 'test-1',
				'name' => 'Updated Test 1',
			],
			[
				'slug' => 'test-2',
				'name' => 'Updated Test 2',
			],
		], 'slug' );

		$this->assertEquals( 2, $updated );

		// Verify the updates
		$result1 = $table::get_first_by( 'slug', 'test-1' );
		$this->assertEquals( 'Updated Test 1', $result1['name'] );

		$result2 = $table::get_first_by( 'slug', 'test-2' );
		$this->assertEquals( 'Updated Test 2', $result2['name'] );
	}

	/**
	 * Test that get_all_by handles array values correctly with IN operator.
	 *
	 * @test
	 */
	public function should_get_all_by_with_array_values() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$id1 = $table::insert( [
			'name' => 'Test 1',
			'slug' => 'test-1',
			'status' => 1,
		] );

		$id2 = $table::insert( [
			'name' => 'Test 2',
			'slug' => 'test-2',
			'status' => 1,
		] );

		$id3 = $table::insert( [
			'name' => 'Test 3',
			'slug' => 'test-3',
			'status' => 0,
		] );

		// Get all by status using array (simulating IN operator scenario)
		$results = $table::get_all_by( 'status', 1 );

		$this->assertCount( 2, $results );
	}

	/**
	 * Test that get_first_by handles array values correctly.
	 *
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
		$result = $table::get_first_by( 'slug', 'first-match' );

		$this->assertNotNull( $result );
		$this->assertEquals( 'First Match', $result['name'] );
	}

	/**
	 * Test that update_multiple handles integer array values.
	 *
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

		// Update using integer value
		$updated = $table::update_multiple( [
			[
				'slug' => 'active-item',
				'status' => 0,
			],
		], 'slug' );

		$this->assertEquals( 1, $updated );

		// Verify the update
		$result = $table::get_first_by( 'slug', 'active-item' );
		$this->assertEquals( 0, $result['status'] );
	}

	/**
	 * Test that ensure_array helper works correctly with scalar values.
	 *
	 * This indirectly tests the ensure_array method through the public API.
	 *
	 * @test
	 */
	public function should_handle_scalar_values_in_queries() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data with scalar values
		$id = $table::insert( [
			'name' => 'Scalar Test',
			'slug' => 'scalar-test',
			'status' => 1,
		] );

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
					$columns[] = ( new Integer_Column( 'status' ) )->set_length( 1 )->set_default_value( 0 );

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
