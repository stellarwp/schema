<?php

namespace StellarWP\Schema\Tests\Tables;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tables\Contracts\Table;
use StellarWP\Schema\Tables\Table_Schema;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Columns\ID;
use StellarWP\Schema\Columns\Integer_Column;
use StellarWP\Schema\Columns\Blob_Column;
use StellarWP\Schema\Columns\String_Column;
use StellarWP\Schema\Columns\Column_Types;
use StellarWP\Schema\Columns\PHP_Types;

class BooleanBlobColumnTest extends SchemaTestCase {
	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_boolean_blob_table()->drop();
	}

	/**
	 * Get a table with Boolean and Blob column types.
	 */
	public function get_boolean_blob_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'bool_blob_test';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-bool-blob';

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// Primary key.
					$columns[] = ( new ID( 'id' ) )
						->set_length( 11 )
						->set_type( Column_Types::INT )
						->set_auto_increment( true );

					// Boolean columns.
					$columns[] = ( new Integer_Column( 'is_active' ) )
						->set_type( Column_Types::TINYINT )
						->set_length( 1 )
						->set_default( 1 )
						->set_php_type( PHP_Types::BOOL );

					$columns[] = ( new Integer_Column( 'is_published' ) )
						->set_type( Column_Types::TINYINT )
						->set_length( 1 )
						->set_default( 0 )
						->set_php_type( PHP_Types::BOOL );

					$columns[] = ( new Integer_Column( 'is_featured' ) )
						->set_type( Column_Types::TINYINT )
						->set_length( 1 )
						->set_nullable( true )
						->set_php_type( PHP_Types::BOOL );

					// Blob columns.
					$columns[] = ( new Blob_Column( 'small_blob' ) )
						->set_type( Column_Types::TINYBLOB )
						->set_php_type( PHP_Types::BLOB );

					$columns[] = ( new Blob_Column( 'regular_blob' ) )
						->set_type( Column_Types::BLOB )
						->set_php_type( PHP_Types::BLOB )
						->set_nullable( true );

					$columns[] = ( new Blob_Column( 'medium_blob' ) )
						->set_type( Column_Types::MEDIUMBLOB )
						->set_php_type( PHP_Types::BLOB );

					$columns[] = ( new Blob_Column( 'large_blob' ) )
						->set_type( Column_Types::LONGBLOB )
						->set_php_type( PHP_Types::BLOB );

					// Regular columns for reference.
					$columns[] = ( new String_Column( 'title' ) )
						->set_length( 255 );

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

	/**
	 * Test table creation with Boolean and Blob columns.
	 *
	 * @test
	 */
	public function should_create_table_with_boolean_and_blob_columns() {
		$table = $this->get_boolean_blob_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );
	}

	/**
	 * Test Boolean column insertion and retrieval.
	 *
	 * @test
	 */
	public function should_handle_boolean_values() {
		global $wpdb;

		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		// Test various boolean representations.
		$test_cases = [
			[
				'input' => [
					'title' => 'Test True Values',
					'is_active' => true,
					'is_published' => 1,
					'is_featured' => 'yes',
					'small_blob' => 'test',
					'medium_blob' => 'test',
					'large_blob' => 'test',
				],
				'expected' => [
					'is_active' => true,
					'is_published' => true,
					'is_featured' => true,
				]
			],
			[
				'input' => [
					'title' => 'Test False Values',
					'is_active' => false,
					'is_published' => 0,
					'is_featured' => '',
					'small_blob' => 'test',
					'medium_blob' => 'test',
					'large_blob' => 'test',
				],
				'expected' => [
					'is_active' => false,
					'is_published' => false,
					'is_featured' => false,
				]
			],
			[
				'input' => [
					'title' => 'Test NULL Boolean',
					'is_active' => 1,
					'is_published' => 0,
					'is_featured' => null,
					'small_blob' => 'test',
					'medium_blob' => 'test',
					'large_blob' => 'test',
				],
				'expected' => [
					'is_active' => true,
					'is_published' => false,
					'is_featured' => null,
				]
			],
		];

		foreach ( $test_cases as $test_case ) {
			$result = $table::insert( $test_case['input'] );
			$this->assertNotFalse( $result );

			$insert_id = $wpdb->insert_id;
			$retrieved = $table::get_by_id( $insert_id );

			$this->assertNotNull( $retrieved );

			// Verify boolean values are properly cast.
			foreach ( $test_case['expected'] as $column => $expected_value ) {
				if ( $expected_value === null ) {
					$this->assertNull( $retrieved[ $column ] );
				} else {
					$this->assertIsBool( $retrieved[ $column ] );
					$this->assertEquals( $expected_value, $retrieved[ $column ] );
				}
			}
		}
	}

	/**
	 * Test Blob column insertion and retrieval.
	 *
	 * @test
	 */
	public function should_handle_blob_data() {
		global $wpdb;

		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		// Test data including binary content.
		$binary_data = "\x00\x01\x02\x03\x04\x05\xFF";
		$image_data = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==' ); // 1x1 PNG
		$text_data = 'Regular text data';
		$json_data = json_encode( [ 'key' => 'value', 'binary' => base64_encode( $binary_data ) ] );

		$data = [
			'title' => 'Test Blob Data',
			'is_active' => 1,
			'is_published' => 0,
			'small_blob' => $binary_data,
			'regular_blob' => $image_data,
			'medium_blob' => $text_data,
			'large_blob' => $json_data,
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		$this->assertNotNull( $retrieved );

		// Verify blob data is properly stored and retrieved.
		$this->assertEquals( $binary_data, $retrieved['small_blob'] );
		$this->assertEquals( $image_data, $retrieved['regular_blob'] );
		$this->assertEquals( $text_data, $retrieved['medium_blob'] );
		$this->assertEquals( $json_data, $retrieved['large_blob'] );
	}

	/**
	 * Test Blob column with NULL values.
	 *
	 * @test
	 */
	public function should_handle_null_blob_values() {
		global $wpdb;

		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		$data = [
			'title' => 'Test NULL Blob',
			'is_active' => 1,
			'is_published' => 0,
			'small_blob' => 'required',
			'regular_blob' => null, // This column is nullable
			'medium_blob' => 'required',
			'large_blob' => 'required',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		$this->assertNotNull( $retrieved );
		$this->assertNull( $retrieved['regular_blob'] );
	}

	/**
	 * Test querying with Boolean values.
	 *
	 * @test
	 */
	public function should_query_by_boolean_values() {
		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		// Insert test data.
		$active_items = [
			[ 'title' => 'Active 1', 'is_active' => 1, 'is_published' => 1, 'small_blob' => 'a', 'medium_blob' => 'a', 'large_blob' => 'a' ],
			[ 'title' => 'Active 2', 'is_active' => true, 'is_published' => 0, 'small_blob' => 'b', 'medium_blob' => 'b', 'large_blob' => 'b' ],
			[ 'title' => 'Active 3', 'is_active' => 1, 'is_published' => 1, 'small_blob' => 'c', 'medium_blob' => 'c', 'large_blob' => 'c' ],
		];

		$inactive_items = [
			[ 'title' => 'Inactive 1', 'is_active' => 0, 'is_published' => 0, 'small_blob' => 'd', 'medium_blob' => 'd', 'large_blob' => 'd' ],
			[ 'title' => 'Inactive 2', 'is_active' => false, 'is_published' => 1, 'small_blob' => 'e', 'medium_blob' => 'e', 'large_blob' => 'e' ],
		];

		foreach ( $active_items as $item ) {
			$table::insert( $item );
		}

		foreach ( $inactive_items as $item ) {
			$table::insert( $item );
		}

		// Query for active items.
		$active_results = $table::get_all_by( 'is_active', true );
		$this->assertCount( 3, $active_results );

		foreach ( $active_results as $result ) {
			$this->assertTrue( $result['is_active'] );
		}

		// Query for inactive items.
		$inactive_results = $table::get_all_by( 'is_active', false );
		$this->assertCount( 2, $inactive_results );

		foreach ( $inactive_results as $result ) {
			$this->assertFalse( $result['is_active'] );
		}

		// Query for published items.
		$published_results = $table::get_all_by( 'is_published', 1 );
		$this->assertCount( 3, $published_results );
	}

	/**
	 * Test updating Boolean and Blob values.
	 *
	 * @test
	 */
	public function should_update_boolean_and_blob_values() {
		global $wpdb;

		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		// Insert initial data.
		$initial_data = [
			'title' => 'Initial',
			'is_active' => true,
			'is_published' => false,
			'is_featured' => true,
			'small_blob' => 'initial_small',
			'regular_blob' => 'initial_regular',
			'medium_blob' => 'initial_medium',
			'large_blob' => 'initial_large',
		];

		$table::insert( $initial_data );
		$insert_id = $wpdb->insert_id;

		// Update with new values.
		$update_data = [
			'id' => $insert_id,
			'is_active' => false,
			'is_published' => true,
			'is_featured' => null,
			'small_blob' => 'updated_small',
			'regular_blob' => null,
			'medium_blob' => 'updated_medium_with_binary_' . "\x00\xFF",
		];

		$result = $table::update_single( $update_data );
		$this->assertTrue( $result );

		// Retrieve and verify.
		$updated = $table::get_by_id( $insert_id );

		$this->assertFalse( $updated['is_active'] );
		$this->assertTrue( $updated['is_published'] );
		$this->assertNull( $updated['is_featured'] );
		$this->assertEquals( 'updated_small', $updated['small_blob'] );
		$this->assertNull( $updated['regular_blob'] );
		$this->assertStringContainsString( 'updated_medium_with_binary', $updated['medium_blob'] );
		$this->assertEquals( 'initial_large', $updated['large_blob'] ); // Should remain unchanged
	}

	/**
	 * Test paginate with Boolean filters.
	 *
	 * @test
	 */
	public function should_paginate_with_boolean_filters() {
		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		// Insert test data.
		for ( $i = 1; $i <= 10; $i++ ) {
			$table::insert( [
				'title' => "Item $i",
				'is_active' => $i % 2 === 0, // Even numbers are active
				'is_published' => $i <= 5,    // First 5 are published
				'is_featured' => $i % 3 === 0 ? 1 : 0, // Every third is featured
				'small_blob' => "blob_$i",
				'medium_blob' => "medium_$i",
				'large_blob' => "large_$i",
			] );
		}

		// Test pagination with boolean filter.
		$args = [
			[
				'column' => 'is_active',
				'value' => 1,
				'operator' => '=',
			],
		];

		$results = $table::paginate( $args, 3, 1 );
		$this->assertCount( 3, $results ); // First page with 3 items

		foreach ( $results as $result ) {
			$this->assertTrue( $result['is_active'] );
		}

		// Test with multiple boolean conditions.
		$args = [
			[
				'column' => 'is_active',
				'value' => 1,
				'operator' => '=',
			],
			[
				'column' => 'is_published',
				'value' => 1,
				'operator' => '=',
			],
		];

		$results = $table::paginate( $args, 10, 1 );
		$filtered_count = 0;
		foreach ( $results as $result ) {
			$this->assertTrue( $result['is_active'] );
			$this->assertTrue( $result['is_published'] );
			$filtered_count++;
		}

		// Items 2 and 4 should match (even and <= 5).
		$this->assertEquals( 2, $filtered_count );
	}

	/**
	 * Test large blob data handling.
	 *
	 * @test
	 */
	public function should_handle_large_blob_data() {
		global $wpdb;

		$table = $this->get_boolean_blob_table();
		Register::table( $table );

		// Create a large binary data (1MB).
		$large_data = str_repeat( 'A', 100 );

		$data = [
			'title' => 'Large Blob Test',
			'is_active' => 1,
			'is_published' => 0,
			'small_blob' => 'small',
			'medium_blob' => 'medium',
			'large_blob' => $large_data,
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		$this->assertNotNull( $retrieved );
		$this->assertEquals( strlen( $large_data ), strlen( $retrieved['large_blob'] ) );
		$this->assertEquals( $large_data, $retrieved['large_blob'] );
	}
}
