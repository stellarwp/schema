<?php

namespace StellarWP\Schema\Tests\Columns;

use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Columns\Boolean_Column;
use StellarWP\Schema\Columns\Blob_Column;
use StellarWP\Schema\Columns\Column_Types;
use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Register;
use StellarWP\Schema\Tables\Contracts\Table;
use StellarWP\Schema\Tables\Table_Schema;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Columns\ID;
use StellarWP\Schema\Columns\String_Column;

class BooleanBlobColumnClassTest extends SchemaTestCase {
	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_column_types_test_table()->drop();
	}

	/**
	 * Get a table using Boolean_Column and Blob_Column classes directly.
	 */
	public function get_column_types_test_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'column_class_test';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-column-class';

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// Primary key
					$columns[] = ( new ID( 'id' ) )
						->set_length( 11 )
						->set_type( Column_Types::INT );

					// Using Boolean_Column class
					$columns[] = ( new Boolean_Column( 'active_flag' ) )
						->set_default( true );

					$columns[] = ( new Boolean_Column( 'published_flag' ) )
						->set_default( false );

					$columns[] = ( new Boolean_Column( 'featured_flag' ) )
						->set_nullable( true );

					// Using different Boolean column types
					$columns[] = ( new Boolean_Column( 'bit_flag' ) )
						->set_type( Column_Types::BIT );

					$columns[] = new Boolean_Column( 'boolean_flag' );

					// Using Blob_Column class with different types
					$columns[] = ( new Blob_Column( 'tiny_data' ) )
						->set_type( Column_Types::TINYBLOB );

					$columns[] = ( new Blob_Column( 'blob_data' ) )
						->set_nullable( true );

					$columns[] = ( new Blob_Column( 'medium_data' ) )
						->set_type( Column_Types::MEDIUMBLOB );

					$columns[] = ( new Blob_Column( 'long_data' ) )
						->set_type( Column_Types::LONGBLOB );

					// Blob column with JSON PHP type
					$columns[] = ( new Blob_Column( 'json_blob_data' ) )
						->set_type( Column_Types::BLOB )
						->set_php_type( PHP_Types::JSON );

					$columns[] = ( new Blob_Column( 'string_blob_data' ) )
						->set_type( Column_Types::BLOB )
						->set_php_type( PHP_Types::STRING );

					// Regular column for reference
					$columns[] = ( new String_Column( 'name' ) )
						->set_length( 255 );

					return new Table_Schema( $table_name, $columns );
				};

				return [
					static::SCHEMA_VERSION => $callable,
				];
			}
		};
	}

	/**
	 * Test Boolean_Column class instantiation and configuration.
	 *
	 * @test
	 */
	public function should_create_boolean_columns_with_proper_types() {
		$column = new Boolean_Column( 'test_bool' );

		// Test default type is BOOLEAN
		$this->assertEquals( Column_Types::BOOLEAN, $column->get_type() );

		// Test PHP type is BOOL
		$this->assertEquals( PHP_Types::BOOL, $column->get_php_type() );

		// Test different boolean types
		$column->set_type( Column_Types::BIT );
		$this->assertEquals( Column_Types::BIT, $column->get_type() );
	}

	/**
	 * Test Blob_Column class instantiation and configuration.
	 *
	 * @test
	 */
	public function should_create_blob_columns_with_proper_types() {
		$column = new Blob_Column( 'test_blob' );

		// Test default type is BLOB
		$this->assertEquals( Column_Types::BLOB, $column->get_type() );

		// Test PHP type is BLOB
		$this->assertEquals( PHP_Types::BLOB, $column->get_php_type() );

		// Test different blob types
		$column->set_type( Column_Types::MEDIUMBLOB );
		$this->assertEquals( Column_Types::MEDIUMBLOB, $column->get_type() );

		// Test JSON PHP type
		$column->set_php_type( PHP_Types::JSON );
		$this->assertEquals( PHP_Types::JSON, $column->get_php_type() );
	}

	/**
	 * Test table creation with column classes.
	 *
	 * @test
	 */
	public function should_create_table_with_column_classes() {
		$table = $this->get_column_types_test_table();
		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Verify columns exist
		$columns = $table::get_columns();
		$this->assertNotNull( $columns->get( 'active_flag' ) );
		$this->assertNotNull( $columns->get( 'published_flag' ) );
		$this->assertNotNull( $columns->get( 'featured_flag' ) );
		$this->assertNotNull( $columns->get( 'bit_flag' ) );
		$this->assertNotNull( $columns->get( 'boolean_flag' ) );
		$this->assertNotNull( $columns->get( 'tiny_data' ) );
		$this->assertNotNull( $columns->get( 'blob_data' ) );
		$this->assertNotNull( $columns->get( 'json_blob_data' ) );
	}

	/**
	 * Test data operations with Boolean_Column defaults.
	 *
	 * @test
	 */
	public function should_respect_boolean_column_defaults() {
		global $wpdb;

		$table = $this->get_column_types_test_table();
		Register::table( $table );

		// Insert with minimal data to test defaults
		$data = [
			'name' => 'Test Defaults',
			'tiny_data' => 'tiny',
			'medium_data' => 'medium',
			'long_data' => 'long',
			'json_blob_data' => json_encode( [ 'test' => 'data' ] ),
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		// active_flag default is true
		$this->assertTrue( $retrieved['active_flag'] );

		// published_flag default is false
		$this->assertFalse( $retrieved['published_flag'] );

		// featured_flag is nullable and should be null
		$this->assertNull( $retrieved['featured_flag'] );
	}

	/**
	 * Test JSON storage in Blob columns.
	 *
	 * @test
	 */
	public function should_handle_json_in_blob_columns() {
		global $wpdb;

		$table = $this->get_column_types_test_table();
		Register::table( $table );

		$complex_json = [
			'nested' => [
				'array' => [ 1, 2, 3 ],
				'object' => [ 'key' => 'value' ],
				'boolean' => true,
				'null' => null,
			],
			'special_chars' => 'Test with "quotes" and \'apostrophes\'',
			'unicode' => '测试 テスト тест',
		];

		$data = [
			'name' => 'JSON Test',
			'active_flag' => true,
			'published_flag' => false,
			'tiny_data' => 'tiny',
			'medium_data' => 'medium',
			'long_data' => 'long',
			'json_blob_data' => $complex_json, // Pass as array, should be encoded
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		// json_blob_data should be decoded back to array
		$this->assertIsArray( $retrieved['json_blob_data'] );
		$this->assertEquals( $complex_json['nested']['array'], $retrieved['json_blob_data']['nested']['array'] );
		$this->assertEquals( $complex_json['special_chars'], $retrieved['json_blob_data']['special_chars'] );
		$this->assertEquals( $complex_json['unicode'], $retrieved['json_blob_data']['unicode'] );
	}


	/**
	 * Test querying with different Boolean column types.
	 *
	 * @test
	 */
	public function should_query_different_boolean_types() {
		$table = $this->get_column_types_test_table();
		Register::table( $table );

		// Insert test data
		$test_data = [
			[
				'name' => 'All True',
				'active_flag' => true,
				'published_flag' => true,
				'featured_flag' => true,
				'bit_flag' => 1,
				'boolean_flag' => true,
				'tiny_data' => 'a',
				'medium_data' => 'a',
				'long_data' => 'a',
				'json_blob_data' => '{}',
			],
			[
				'name' => 'All False',
				'active_flag' => false,
				'published_flag' => false,
				'featured_flag' => false,
				'bit_flag' => 0,
				'boolean_flag' => false,
				'tiny_data' => 'b',
				'medium_data' => 'b',
				'long_data' => 'b',
				'json_blob_data' => '{}',
			],
			[
				'name' => 'Mixed',
				'active_flag' => true,
				'published_flag' => false,
				'featured_flag' => null,
				'bit_flag' => 1,
				'boolean_flag' => false,
				'tiny_data' => 'c',
				'medium_data' => 'c',
				'long_data' => 'c',
				'json_blob_data' => '{}',
			],
		];

		foreach ( $test_data as $data ) {
			$table::insert( $data );
		}

		// Query by different boolean columns
		$active_results = $table::get_all_by( 'active_flag', true );
		$this->assertCount( 2, $active_results ); // "All True" and "Mixed"

		$bit_true_results = $table::get_all_by( 'bit_flag', 1 );
		$this->assertCount( 2, $bit_true_results );

		$boolean_false_results = $table::get_all_by( 'boolean_flag', false );
		$this->assertCount( 2, $boolean_false_results ); // "All False" and "Mixed"

		// Test nullable featured_flag
		$featured_true = $table::get_all_by( 'featured_flag', true );
		$this->assertCount( 1, $featured_true );
		$this->assertEquals( 'All True', $featured_true[0]['name'] );
	}

	/**
	 * Test update operations with Boolean and Blob columns.
	 *
	 * @test
	 */
	public function should_update_boolean_and_blob_columns() {
		global $wpdb;

		$table = $this->get_column_types_test_table();
		Register::table( $table );

		// Insert initial data
		$initial = [
			'name' => 'Update Test',
			'active_flag' => true,
			'published_flag' => false,
			'bit_flag' => 1,
			'boolean_flag' => true,
			'tiny_data' => 'initial_tiny',
			'blob_data' => 'initial_blob',
			'medium_data' => 'initial_medium',
			'long_data' => 'initial_long',
			'json_blob_data' => json_encode( [ 'version' => 1 ] ),
		];

		$table::insert( $initial );
		$insert_id = $wpdb->insert_id;

		// Update with new values
		$update = [
			'id' => $insert_id,
			'active_flag' => false,
			'published_flag' => true,
			'bit_flag' => 0,
			'boolean_flag' => false,
			'blob_data' => 'updated_blob_' . str_repeat( 'X', 1000 ),
			'json_blob_data' => json_encode( [ 'version' => 2, 'updated' => true ] ),
		];

		$result = $table::update_single( $update );
		$this->assertTrue( $result );

		// Retrieve and verify
		$updated = $table::get_by_id( $insert_id );

		// Check boolean updates
		$this->assertFalse( $updated['active_flag'] );
		$this->assertTrue( $updated['published_flag'] );
		$this->assertFalse( $updated['bit_flag'] );
		$this->assertFalse( $updated['boolean_flag'] );

		// Check blob updates
		$this->assertStringContainsString( 'updated_blob_', $updated['blob_data'] );
		$this->assertStringContainsString( str_repeat( 'X', 1000 ), $updated['blob_data'] );

		// Check JSON blob update
		$json_data = $updated['json_blob_data'];
		$this->assertEquals( 2, $json_data['version'] );
		$this->assertTrue( $json_data['updated'] );

		// Check unchanged values
		$this->assertEquals( 'initial_tiny', $updated['tiny_data'] );
		$this->assertEquals( 'initial_medium', $updated['medium_data'] );
	}
}
