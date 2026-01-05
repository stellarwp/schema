<?php

namespace StellarWP\Schema\Tests\Traits;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;
use StellarWP\Schema\Tests\Traits\Query_Snapshot_Assertions;
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
	use Query_Snapshot_Assertions;

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

		$table::insert( [
			'name' => 'Test 2',
			'slug' => 'test-2',
			'status' => 1,
		] );

		$table::insert( [
			'name' => 'Test 3',
			'slug' => 'test-3',
			'status' => 0,
		] );

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
	public function should_handle_empty_array() {
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

		$table::insert( [
			'name' => 'Test 3',
			'slug' => 'test-3',
			'status' => 0,
		] );

		$results = $table::get_all_by( 'status', [], 'IN' );

		$this->assertEmpty( $results );
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
	 * @test
	 */
	public function should_paginate_with_simple_where_clause() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [ 'name' => 'Item 1', 'slug' => 'item-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Item 2', 'slug' => 'item-2', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Item 3', 'slug' => 'item-3', 'status' => 0 ] );
		$table::insert( [ 'name' => 'Item 4', 'slug' => 'item-4', 'status' => 1 ] );

		// Query with simple where clause
		$results = $table::paginate(
			[
				[
					'column'   => 'status',
					'value'    => 1,
					'operator' => '=',
				],
			],
			10,
			1
		);

		$this->assertCount( 3, $results );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_paginate_with_sub_where_clauses_using_or() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [ 'name' => 'Alpha', 'slug' => 'alpha', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Beta', 'slug' => 'beta', 'status' => 0 ] );
		$table::insert( [ 'name' => 'Gamma', 'slug' => 'gamma', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Delta', 'slug' => 'delta', 'status' => 0 ] );

		// Query: WHERE (slug = 'alpha' OR slug = 'beta')
		$results = $table::paginate(
			[
				[
					'query_operator' => 'OR',
					[
						'column'   => 'slug',
						'value'    => 'alpha',
						'operator' => '=',
					],
					[
						'column'   => 'slug',
						'value'    => 'beta',
						'operator' => '=',
					],
				],
			],
			10,
			1
		);

		$this->assertCount( 2, $results );
		$slugs = array_column( $results, 'slug' );
		$this->assertContains( 'alpha', $slugs );
		$this->assertContains( 'beta', $slugs );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_paginate_with_nested_sub_where_clauses() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [ 'name' => 'Active Alpha', 'slug' => 'alpha', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Inactive Alpha', 'slug' => 'alpha-inactive', 'status' => 0 ] );
		$table::insert( [ 'name' => 'Active Beta', 'slug' => 'beta', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Inactive Beta', 'slug' => 'beta-inactive', 'status' => 0 ] );
		$table::insert( [ 'name' => 'Active Gamma', 'slug' => 'gamma', 'status' => 1 ] );

		// Query: WHERE (slug = 'alpha' OR slug = 'beta') AND status = 1
		$results = $table::paginate(
			[
				[
					'query_operator' => 'OR',
					[
						'column'   => 'slug',
						'value'    => 'alpha',
						'operator' => '=',
					],
					[
						'column'   => 'slug',
						'value'    => 'beta',
						'operator' => '=',
					],
				],
				[
					'column'   => 'status',
					'value'    => 1,
					'operator' => '=',
				],
			],
			10,
			1
		);

		$this->assertCount( 2, $results );
		$slugs = array_column( $results, 'slug' );
		$this->assertContains( 'alpha', $slugs );
		$this->assertContains( 'beta', $slugs );

		// Verify all results have status = 1
		foreach ( $results as $result ) {
			$this->assertEquals( 1, $result['status'] );
		}
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_paginate_with_multiple_sub_where_groups() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [ 'name' => 'A', 'slug' => 'a', 'status' => 1 ] );
		$table::insert( [ 'name' => 'B', 'slug' => 'b', 'status' => 0 ] );
		$table::insert( [ 'name' => 'C', 'slug' => 'c', 'status' => 1 ] );
		$table::insert( [ 'name' => 'D', 'slug' => 'd', 'status' => 0 ] );

		// Query with OR at the top level: WHERE (slug = 'a') OR (slug = 'b')
		$results = $table::paginate(
			[
				'query_operator' => 'OR',
				[
					'column'   => 'slug',
					'value'    => 'a',
					'operator' => '=',
				],
				[
					'column'   => 'slug',
					'value'    => 'b',
					'operator' => '=',
				],
			],
			10,
			1
		);

		$this->assertCount( 2, $results );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_count_total_items_with_sub_where_clauses() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [ 'name' => 'X', 'slug' => 'x', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Y', 'slug' => 'y', 'status' => 0 ] );
		$table::insert( [ 'name' => 'Z', 'slug' => 'z', 'status' => 1 ] );

		// Count with sub-where: WHERE (slug = 'x' OR slug = 'y')
		$total = $table::get_total_items(
			[
				[
					'query_operator' => 'OR',
					[
						'column'   => 'slug',
						'value'    => 'x',
						'operator' => '=',
					],
					[
						'column'   => 'slug',
						'value'    => 'y',
						'operator' => '=',
					],
				],
			]
		);

		$this->assertEquals( 2, $total );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_handle_sub_where_with_different_operators() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data
		$table::insert( [ 'name' => 'First', 'slug' => 'first', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Second', 'slug' => 'second', 'status' => 2 ] );
		$table::insert( [ 'name' => 'Third', 'slug' => 'third', 'status' => 3 ] );
		$table::insert( [ 'name' => 'Fourth', 'slug' => 'fourth', 'status' => 4 ] );

		// Query: WHERE (status > 1 AND status < 4)
		$results = $table::paginate(
			[
				[
					'query_operator' => 'AND',
					[
						'column'   => 'status',
						'value'    => 1,
						'operator' => '>',
					],
					[
						'column'   => 'status',
						'value'    => 4,
						'operator' => '<',
					],
				],
			],
			10,
			1
		);

		$this->assertCount( 2, $results );
		$statuses = array_column( $results, 'status' );
		$this->assertContains( 2, $statuses );
		$this->assertContains( 3, $statuses );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_use_stellarwp_schema_hook_prefix() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Hook Test', 'slug' => 'hook-test', 'status' => 1 ] );

		$action_count_before = did_action( 'stellarwp_schema_custom_table_query_pre_results' );
		$action_count_after = did_action( 'stellarwp_schema_custom_table_query_post_results' );
		$filter_count_before = did_filter( 'stellarwp_schema_custom_table_query_results' );
		$filter_count_after = did_filter( 'stellarwp_schema_custom_table_query_where' );

		$table::paginate( [], 10, 1 );

		$this->assertSame( $action_count_before + 1, did_action( 'stellarwp_schema_custom_table_query_pre_results' ), 'stellarwp_schema_custom_table_query_pre_results action should fire' );
		$this->assertSame( $action_count_after + 1, did_action( 'stellarwp_schema_custom_table_query_post_results' ), 'stellarwp_schema_custom_table_query_post_results action should fire' );
		$this->assertSame( $filter_count_before + 1, did_filter( 'stellarwp_schema_custom_table_query_results' ), 'stellarwp_schema_custom_table_query_results filter should fire' );
		$this->assertSame( $filter_count_after + 1, did_filter( 'stellarwp_schema_custom_table_query_where' ), 'stellarwp_schema_custom_table_query_where filter should fire' );
	}

	/**
	 * @test
	 */
	public function should_ignore_sub_where_with_missing_column_key() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test 1', 'slug' => 'test-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Test 2', 'slug' => 'test-2', 'status' => 0 ] );

		add_filter( 'doing_it_wrong_trigger_error', '__return_false' );

		$doing_it_wrong_called = false;
		add_action( 'doing_it_wrong_run', function() use ( &$doing_it_wrong_called ) {
			$doing_it_wrong_called = true;
		} );

		// Malformed: missing 'column' key.
		$results = $table::paginate(
			[
				[
					'value'    => 'test-1',
					'operator' => '=',
				],
			],
			10,
			1
		);

		$this->assertTrue( $doing_it_wrong_called, '_doing_it_wrong should be called' );

		// Should return all rows since the malformed filter is ignored.
		$this->assertCount( 2, $results );

		// Query should not contain WHERE clause.
		$this->assertStringNotContainsString( 'WHERE', $this->get_captured_query() );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_handle_sub_where_with_missing_value_key() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test 1', 'slug' => 'test-1', 'status' => 1 ] );
		$table::insert( [ 'name' => '', 'slug' => 'test-2', 'status' => 0 ] );

		$doing_it_wrong_called = false;

		add_filter( 'doing_it_wrong_trigger_error', '__return_false' );

		add_action( 'doing_it_wrong_run', function () use ( &$doing_it_wrong_called ) {
			$doing_it_wrong_called = true;
		} );

		// Missing 'value' key - should default to checking column is not empty.
		$results = $table::paginate(
			[
				[
					'column' => 'name',
				],
			],
			10,
			1
		);

		$this->assertFalse( $doing_it_wrong_called, '_doing_it_wrong should not be called' );

		// Should return only the row with non-empty name.
		$this->assertCount( 1, $results );
		$this->assertEquals( 'Test 1', $results[0]['name'] );

		// Query should contain WHERE clause with != ''.
		$this->assertQueryContains( "a.name != ''" );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_normalize_invalid_operator_to_equals() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test 1', 'slug' => 'test-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Test 2', 'slug' => 'test-2', 'status' => 0 ] );

		// Invalid operator should be normalized to '='.
		$results = $table::paginate(
			[
				[
					'column'   => 'slug',
					'value'    => 'test-1',
					'operator' => 'INVALID_OP',
				],
			],
			10,
			1
		);

		$this->assertCount( 1, $results );
		$this->assertEquals( 'test-1', $results[0]['slug'] );

		// Should use '=' operator.
		$this->assertQueryContains( "a.slug = 'test-1'" );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_ignore_sub_where_with_invalid_column_name() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test 1', 'slug' => 'test-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Test 2', 'slug' => 'test-2', 'status' => 0 ] );

		// Column 'nonexistent' does not exist.
		$results = $table::paginate(
			[
				[
					'column'   => 'nonexistent_column',
					'value'    => 'test-1',
					'operator' => '=',
				],
			],
			10,
			1
		);

		// Should return all rows since invalid column filter is ignored.
		$this->assertCount( 2, $results );

		// Query should not contain WHERE clause.
		$this->assertStringNotContainsString( 'WHERE', $this->get_captured_query() );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_ignore_nested_sub_where_with_missing_column() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Alpha', 'slug' => 'alpha', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Beta', 'slug' => 'beta', 'status' => 0 ] );

		// Nested sub-where with one valid and one invalid entry.
		$results = $table::paginate(
			[
				[
					'query_operator' => 'OR',
					[
						'column'   => 'slug',
						'value'    => 'alpha',
						'operator' => '=',
					],
					[
						// Missing 'column' key - should be ignored.
						'value'    => 'beta',
						'operator' => '=',
					],
				],
			],
			10,
			1
		);

		// Should return only alpha since the invalid sub-where is ignored.
		$this->assertCount( 1, $results );
		$this->assertEquals( 'alpha', $results[0]['slug'] );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_normalize_invalid_query_operator_in_sub_where() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test 1', 'slug' => 'test-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Test 2', 'slug' => 'test-2', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Test 3', 'slug' => 'test-3', 'status' => 0 ] );

		// Invalid query_operator should be normalized to 'AND'.
		$results = $table::paginate(
			[
				[
					'query_operator' => 'INVALID',
					[
						'column'   => 'slug',
						'value'    => 'test-1',
						'operator' => '=',
					],
					[
						'column'   => 'status',
						'value'    => 1,
						'operator' => '=',
					],
				],
			],
			10,
			1
		);

		// With AND operator, only 'test-1' matches both conditions.
		$this->assertCount( 1, $results );

		// Should use 'AND' operator (the default).
		$this->assertQueryContains( "AND" );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_handle_deeply_nested_sub_wheres_four_levels() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		// Insert test data covering various combinations.
		$table::insert( [ 'name' => 'A1', 'slug' => 'a1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'A2', 'slug' => 'a2', 'status' => 1 ] );
		$table::insert( [ 'name' => 'B1', 'slug' => 'b1', 'status' => 0 ] );
		$table::insert( [ 'name' => 'B2', 'slug' => 'b2', 'status' => 0 ] );
		$table::insert( [ 'name' => 'C1', 'slug' => 'c1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'C2', 'slug' => 'c2', 'status' => 0 ] );

		// Level 4 deep nesting:
		// WHERE (
		//   (
		//     (slug = 'a1' OR slug = 'a2')
		//     AND
		//     (status = 1)
		//   )
		//   OR
		//   (slug = 'b1')
		// )
		$results = $table::paginate(
			[
				[
					'query_operator' => 'OR',
					// Level 2
					[
						'query_operator' => 'AND',
						// Level 3
						[
							'query_operator' => 'OR',
							// Level 4
							[
								'column'   => 'slug',
								'value'    => 'a1',
								'operator' => '=',
							],
							[
								'column'   => 'slug',
								'value'    => 'a2',
								'operator' => '=',
							],
						],
						[
							'column'   => 'status',
							'value'    => 1,
							'operator' => '=',
						],
					],
					// OR with this
					[
						'column'   => 'slug',
						'value'    => 'b1',
						'operator' => '=',
					],
				],
			],
			10,
			1
		);

		// Should match: a1 (status=1), a2 (status=1), b1 (any status).
		$this->assertCount( 3, $results );
		$slugs = array_column( $results, 'slug' );
		$this->assertContains( 'a1', $slugs );
		$this->assertContains( 'a2', $slugs );
		$this->assertContains( 'b1', $slugs );

		// Verify the query structure has proper nesting.
		$query = $this->get_captured_query();
		$this->assertStringContainsString( 'WHERE', $query );
		// Should contain nested parentheses.
		$this->assertStringContainsString( '((', $query );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_handle_complex_four_level_nesting_with_all_operators() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Item 1', 'slug' => 'item-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Item 2', 'slug' => 'item-2', 'status' => 2 ] );
		$table::insert( [ 'name' => 'Item 3', 'slug' => 'item-3', 'status' => 3 ] );
		$table::insert( [ 'name' => 'Item 4', 'slug' => 'item-4', 'status' => 4 ] );
		$table::insert( [ 'name' => 'Item 5', 'slug' => 'item-5', 'status' => 5 ] );

		// Complex query:
		// WHERE (
		//   (
		//     (status >= 2 AND status <= 4)
		//     OR
		//     (slug = 'item-1')
		//   )
		//   AND
		//   (status != 3)
		// )
		$results = $table::paginate(
			[
				[
					'query_operator' => 'AND',
					// Level 2
					[
						'query_operator' => 'OR',
						// Level 3
						[
							'query_operator' => 'AND',
							// Level 4
							[
								'column'   => 'status',
								'value'    => 2,
								'operator' => '>=',
							],
							[
								'column'   => 'status',
								'value'    => 4,
								'operator' => '<=',
							],
						],
						[
							'column'   => 'slug',
							'value'    => 'item-1',
							'operator' => '=',
						],
					],
					[
						'column'   => 'status',
						'value'    => 3,
						'operator' => '!=',
					],
				],
			],
			10,
			1
		);

		// Should match: item-1 (status=1, not 3), item-2 (status=2, in range, not 3), item-4 (status=4, in range, not 3).
		$this->assertCount( 3, $results );
		$slugs = array_column( $results, 'slug' );
		$this->assertContains( 'item-1', $slugs );
		$this->assertContains( 'item-2', $slugs );
		$this->assertContains( 'item-4', $slugs );
		$this->assertNotContains( 'item-3', $slugs ); // Excluded by != 3.
		$this->assertNotContains( 'item-5', $slugs ); // Not in range and not item-1.
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_capture_query_with_paginate_filter() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test', 'slug' => 'test', 'status' => 1 ] );

		$table::paginate(
			[
				[
					'column'   => 'status',
					'value'    => 1,
					'operator' => '=',
				],
			],
			10,
			1
		);

		$query = $this->get_captured_query();

		// Verify query structure.
		$this->assertNotEmpty( $query );
		$this->assertStringContainsString( 'SELECT', $query );
		$this->assertStringContainsString( 'FROM', $query );
		$this->assertStringContainsString( 'WHERE', $query );
		$this->assertStringContainsString( 'a.status = 1', $query );
		$this->assertStringContainsString( 'LIMIT', $query );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_produce_correct_query_for_simple_sub_where() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test', 'slug' => 'test', 'status' => 1 ] );

		$table::paginate(
			[
				'orderby' => 'id',
				'order'   => 'ASC',
				[
					'column'   => 'slug',
					'value'    => 'test',
					'operator' => '=',
				],
			],
			20,
			1
		);

		$this->assertQueryContains( "WHERE a.slug = 'test'" );
		$this->assertQueryContains( "ORDER BY a.id ASC" );
		$this->assertQueryContains( "LIMIT 0, 20" );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_produce_correct_query_for_or_sub_where() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'A', 'slug' => 'a', 'status' => 1 ] );
		$table::insert( [ 'name' => 'B', 'slug' => 'b', 'status' => 0 ] );

		$table::paginate(
			[
				[
					'query_operator' => 'OR',
					[
						'column'   => 'slug',
						'value'    => 'a',
						'operator' => '=',
					],
					[
						'column'   => 'slug',
						'value'    => 'b',
						'operator' => '=',
					],
				],
			],
			10,
			1
		);

		// Verify the query has OR grouped in parentheses.
		$this->assertQueryContains( "(a.slug = 'a' OR a.slug = 'b')" );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_produce_correct_query_for_nested_sub_where() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test', 'slug' => 'test', 'status' => 1 ] );

		// WHERE ((slug = 'a' OR slug = 'b') AND status = 1)
		$table::paginate(
			[
				[
					'query_operator' => 'OR',
					[
						'column'   => 'slug',
						'value'    => 'a',
						'operator' => '=',
					],
					[
						'column'   => 'slug',
						'value'    => 'b',
						'operator' => '=',
					],
				],
				[
					'column'   => 'status',
					'value'    => 1,
					'operator' => '=',
				],
			],
			10,
			1
		);

		// Verify the query structure.
		$query = $this->get_captured_query();
		$this->assertStringContainsString( "(a.slug = 'a' OR a.slug = 'b')", $query );
		$this->assertStringContainsString( "a.status = 1", $query );
		$this->assertStringContainsString( " AND ", $query );
		$this->assertCapturedQueryMatchesSnapshot();
	}

	/**
	 * @test
	 */
	public function should_handle_empty_sub_where_group() {
		$table = $this->get_query_test_table();
		Register::table( $table );

		$table::insert( [ 'name' => 'Test 1', 'slug' => 'test-1', 'status' => 1 ] );
		$table::insert( [ 'name' => 'Test 2', 'slug' => 'test-2', 'status' => 0 ] );

		// Empty sub-where group should be ignored.
		$results = $table::paginate(
			[
				[
					'query_operator' => 'OR',
					// Empty - no conditions.
				],
				[
					'column'   => 'status',
					'value'    => 1,
					'operator' => '=',
				],
			],
			10,
			1
		);

		$this->assertCount( 1, $results );
		$this->assertEquals( 'test-1', $results[0]['slug'] );
		$this->assertCapturedQueryMatchesSnapshot();
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
