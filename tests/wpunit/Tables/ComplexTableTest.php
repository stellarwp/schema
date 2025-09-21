<?php

namespace StellarWP\Schema\Tests\Tables;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tables\Contracts\Table;
use StellarWP\Schema\Tables\Table_Schema;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Collections\Index_Collection;
use StellarWP\Schema\Columns\ID;
use StellarWP\Schema\Columns\Integer_Column;
use StellarWP\Schema\Columns\Float_Column;
use StellarWP\Schema\Columns\String_Column;
use StellarWP\Schema\Columns\Text_Column;
use StellarWP\Schema\Columns\Datetime_Column;
use StellarWP\Schema\Columns\Created_At;
use StellarWP\Schema\Columns\Updated_At;
use StellarWP\Schema\Columns\Last_Changed;
use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Indexes\Classic_Index;
use StellarWP\Schema\Indexes\Unique_Key;
use DateTime;

class ComplexTableTest extends SchemaTestCase {
	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_comprehensive_table()->drop();
		$this->get_indexed_table()->drop();
		$this->get_timestamp_table()->drop();
		$this->get_created_at_table()->drop();
		$this->get_updated_at_table()->drop();
	}

	/**
	 * Get a table with all column types.
	 */
	public function get_comprehensive_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'comprehensive_columns';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-comprehensive';

			public function get_schema_history(): array {
				$columns = new Column_Collection();

				// Primary key with auto increment
				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column::COLUMN_TYPE_BIGINT )
					->set_auto_increment( true );

				// Integer types
				$columns[] = ( new Integer_Column( 'tinyint_col' ) )
					->set_type( Column::COLUMN_TYPE_TINYINT )
					->set_length( 3 )
					->set_signed( false )
					->set_default( 0 );

				$columns[] = ( new Integer_Column( 'smallint_col' ) )
					->set_type( Column::COLUMN_TYPE_SMALLINT )
					->set_length( 5 )
					->set_signed( true )
					->set_nullable( true );

				$columns[] = ( new Integer_Column( 'mediumint_col' ) )
					->set_type( Column::COLUMN_TYPE_MEDIUMINT )
					->set_length( 8 )
					->set_default( 100 );

				$columns[] = ( new Integer_Column( 'int_col' ) )
					->set_type( Column::COLUMN_TYPE_INT )
					->set_length( 11 )
					->set_signed( true )
					->set_is_index( true );

				$columns[] = ( new Integer_Column( 'bigint_col' ) )
					->set_type( Column::COLUMN_TYPE_BIGINT )
					->set_length( 20 )
					->set_signed( false );

				// Float types
				// For FLOAT(10,2) - 10 total digits, 2 decimal places
				$columns[] = ( new Float_Column( 'float_col' ) )
					->set_type( Column::COLUMN_TYPE_FLOAT )
					->set_length( 10 )
					->set_precision( 2 )
					->set_default( 0.0 );

				// For DECIMAL(15,4) - 15 total digits, 4 decimal places
				$columns[] = ( new Float_Column( 'decimal_col' ) )
					->set_type( Column::COLUMN_TYPE_DECIMAL )
					->set_length( 15 )
					->set_precision( 4 )
					->set_nullable( true );

				// For DOUBLE(22,8) - 22 total digits, 8 decimal places
				$columns[] = ( new Float_Column( 'double_col' ) )
					->set_type( Column::COLUMN_TYPE_DOUBLE )
					->set_length( 22 )
					->set_precision( 8 );

				// String types
				$columns[] = ( new String_Column( 'char_col' ) )
					->set_type( Column::COLUMN_TYPE_CHAR )
					->set_length( 10 )
					->set_default( 'DEFAULT' );

				$columns[] = ( new String_Column( 'varchar_col' ) )
					->set_type( Column::COLUMN_TYPE_VARCHAR )
					->set_length( 255 )
					->set_searchable( true )
					->set_is_unique( true );

				// Text types
				$columns[] = ( new Text_Column( 'tinytext_col' ) )
					->set_type( Column::COLUMN_TYPE_TINYTEXT );

				$columns[] = ( new Text_Column( 'text_col' ) )
					->set_type( Column::COLUMN_TYPE_TEXT )
					->set_nullable( true );

				$columns[] = ( new Text_Column( 'mediumtext_col' ) )
					->set_type( Column::COLUMN_TYPE_MEDIUMTEXT );

				$columns[] = ( new Text_Column( 'longtext_col' ) )
					->set_type( Column::COLUMN_TYPE_LONGTEXT );

				// Datetime types
				$columns[] = ( new Datetime_Column( 'date_col' ) )
					->set_type( Column::COLUMN_TYPE_DATE )
					->set_nullable( true );

				$columns[] = ( new Datetime_Column( 'datetime_col' ) )
					->set_type( Column::COLUMN_TYPE_DATETIME )
					->set_default( '0000-00-00 00:00:00' );

				$columns[] = new Last_Changed( 'last_changed' );

				// Boolean column
				$columns[] = ( new Integer_Column( 'is_active' ) )
					->set_type( Column::COLUMN_TYPE_TINYINT )
					->set_length( 1 )
					->set_default( 1 )
					->set_php_type( Column::PHP_TYPE_BOOL );

				// JSON column (stored as text)
				$columns[] = ( new Text_Column( 'json_data' ) )
					->set_type( Column::COLUMN_TYPE_TEXT )
					->set_php_type( Column::PHP_TYPE_JSON );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
	}

	/**
	 * Get a table with all index types.
	 */
	public function get_indexed_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'indexed_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-indexed';

			public function get_schema_history(): array {
				$columns = new Column_Collection();

				// Primary key
				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column::COLUMN_TYPE_INT )
					->set_auto_increment( true );

				// Columns for various indexes
				$columns[] = ( new String_Column( 'unique_email' ) )
					->set_length( 255 )
					->set_is_unique( true );

				$columns[] = ( new String_Column( 'indexed_slug' ) )
					->set_length( 200 )
					->set_is_index( true );

				$columns[] = ( new Integer_Column( 'user_id' ) )
					->set_type( Column::COLUMN_TYPE_INT )
					->set_length( 11 )
					->set_is_index( true );

				$columns[] = ( new String_Column( 'category' ) )
					->set_length( 100 );

				$columns[] = ( new String_Column( 'tag' ) )
					->set_length( 100 );

				$columns[] = ( new Text_Column( 'searchable_content' ) )
					->set_type( Column::COLUMN_TYPE_TEXT );

				$columns[] = ( new String_Column( 'title' ) )
					->set_length( 255 );

				$columns[] = ( new Text_Column( 'description' ) )
					->set_type( Column::COLUMN_TYPE_TEXT );

				$columns[] = ( new Integer_Column( 'status' ) )
					->set_type( Column::COLUMN_TYPE_TINYINT )
					->set_length( 1 )
					->set_default( 1 );

				$columns[] = ( new Datetime_Column( 'published_at' ) )
					->set_type( Column::COLUMN_TYPE_DATETIME );

				// Define additional indexes
				$indexes = new Index_Collection();

				// Composite index
				$indexes[] = ( new Classic_Index( 'idx_category_tag' ) )
					->set_columns( 'category', 'tag' );

				// Another composite with different order
				$indexes[] = ( new Classic_Index( 'idx_status_published' ) )
					->set_columns( 'status', 'published_at' );

				// Unique composite key
				$indexes[] = ( new Unique_Key( 'uk_user_category' ) )
					->set_columns( 'user_id', 'category' );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns, $indexes ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
	}

	/**
	 * Get a table with timestamp column that auto-updates (MySQL 5.5 compatible - only one CURRENT_TIMESTAMP).
	 */
	public function get_timestamp_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'timestamp_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-timestamp';

			public function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column::COLUMN_TYPE_INT );

				$columns[] = ( new String_Column( 'title' ) )
					->set_length( 255 );

				$columns[] = ( new Datetime_Column( 'timestamp_col' ) )
					->set_type( Column::COLUMN_TYPE_TIMESTAMP )
					->set_default( 'CURRENT_TIMESTAMP' )
					->set_on_update( 'CURRENT_TIMESTAMP' );

				$columns[] = ( new Datetime_Column( 'created_date' ) )
					->set_type( Column::COLUMN_TYPE_DATETIME )
					->set_default( '0000-00-00 00:00:00' );

				$columns[] = ( new Datetime_Column( 'updated_date' ) )
					->set_type( Column::COLUMN_TYPE_DATETIME )
					->set_nullable( true )
					->set_default( 'NULL' );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
	}

	/**
	 * Get a table with Created_At column.
	 */
	public function get_created_at_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'created_at_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-created-at';

			public function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column::COLUMN_TYPE_INT )
					->set_auto_increment( true );

				$columns[] = ( new String_Column( 'name' ) )
					->set_length( 100 );

				// Created_At column
				$columns[] = new Created_At( 'created_at' );

				// Regular datetime for comparison
				$columns[] = ( new Datetime_Column( 'other_date' ) )
					->set_type( Column::COLUMN_TYPE_DATETIME )
					->set_nullable( true );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
	}

	/**
	 * Get a table with Updated_At column.
	 */
	public function get_updated_at_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'updated_at_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-updated-at';

			public function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column::COLUMN_TYPE_INT );

				$columns[] = ( new String_Column( 'content' ) )
					->set_length( 255 );

				$columns[] = new Updated_At( 'updated_at' );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
	}

	/**
	 * Test comprehensive table creation and structure.
	 *
	 * @test
	 */
	public function should_create_comprehensive_table() {
		$table = $this->get_comprehensive_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );
	}

	/**
	 * Test indexed table creation and structure.
	 *
	 * @test
	 */
	public function should_create_indexed_table() {
		$table = $this->get_indexed_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Verify indexes exist
		$this->assertTrue( $table->has_index( 'indexed_slug' ) );
		$this->assertTrue( $table->has_index( 'user_id' ) );
		$this->assertTrue( $table->has_index( 'unique_email' ) );
		$this->assertTrue( $table->has_index( 'idx_category_tag' ) );
		$this->assertTrue( $table->has_index( 'idx_status_published' ) );
		$this->assertTrue( $table->has_index( 'uk_user_category' ) );
	}

	/**
	 * Test data insertion and retrieval with proper types.
	 *
	 * @test
	 */
	public function should_insert_and_retrieve_data_with_correct_types() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert test data
		$data = [
			'tinyint_col' => 127,
			'smallint_col' => -1000,
			'mediumint_col' => 50000,
			'int_col' => 2147483647,
			'bigint_col' => '9223372036854775807',
			'float_col' => 123.45,
			'decimal_col' => '1234.5678',
			'double_col' => 123456.78901234,
			'char_col' => 'FIXED',
			'varchar_col' => 'Variable length string',
			'tinytext_col' => 'Tiny text content',
			'text_col' => 'Regular text content with more data',
			'mediumtext_col' => str_repeat( 'Medium text ', 100 ),
			'longtext_col' => str_repeat( 'Long text content ', 1000 ),
			'date_col' => '2024-01-15',
			'datetime_col' => '2024-01-15 14:30:00',
			'is_active' => 1,
			'json_data' => json_encode( [ 'key' => 'value', 'nested' => [ 'data' => true ] ] ),
		];

		$inserted = $wpdb->insert( $table_name, $data );
		$this->assertNotFalse( $inserted );

		$insert_id = $wpdb->insert_id;
		$this->assertGreaterThan( 0, $insert_id );

		// Retrieve and verify data
		$result = $table::get_by_id( $insert_id );

		$this->assertNotNull( $result );

		// Verify integer types
		$this->assertSame( $insert_id, (int) $result['id'] );
		$this->assertSame( 127, (int) $result['tinyint_col'] );
		$this->assertSame( -1000, (int) $result['smallint_col'] );
		$this->assertSame( 50000, (int) $result['mediumint_col'] );
		$this->assertSame( 2147483647, (int) $result['int_col'] );
		$this->assertEquals( '9223372036854775807', $result['bigint_col'] );

		// Verify float types
		$this->assertEqualsWithDelta( 123.45, (float) $result['float_col'], 0.01 );
		$this->assertEqualsWithDelta( 1234.5678, (float) $result['decimal_col'], 0.0001 );
		$this->assertEqualsWithDelta( 123456.78901234, (float) $result['double_col'], 0.00001 );

		// Verify string types
		$this->assertEquals( 'FIXED', trim( $result['char_col'] ) );
		$this->assertEquals( 'Variable length string', $result['varchar_col'] );

		// Verify text types
		$this->assertEquals( 'Tiny text content', $result['tinytext_col'] );
		$this->assertEquals( 'Regular text content with more data', $result['text_col'] );
		$this->assertStringContainsString( 'Medium text', $result['mediumtext_col'] );
		$this->assertStringContainsString( 'Long text content', $result['longtext_col'] );

		// Verify datetime types
		$this->assertEquals( '2024-01-15', $result['date_col']->format( 'Y-m-d' ) );
		$this->assertEquals( '2024-01-15 14:30:00', $result['datetime_col']->format( 'Y-m-d H:i:s' ) );

		// Verify special columns - only last_changed is in comprehensive table
		$this->assertNotNull( $result['last_changed'] );
		$this->assertInstanceOf( DateTime::class, $result['last_changed'] );

		// Verify boolean transformation
		$this->assertIsBool( $result['is_active'] );
		$this->assertTrue( $result['is_active'] );

		// Verify JSON transformation
		$this->assertIsArray( $result['json_data'] );
		$this->assertEquals( 'value', $result['json_data']['key'] );
		$this->assertTrue( $result['json_data']['nested']['data'] );
	}

	/**
	 * Test nullable columns.
	 *
	 * @test
	 */
	public function should_handle_nullable_columns() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert with NULL values
		$data = [
			'smallint_col' => null,
			'decimal_col' => null,
			'text_col' => null,
			'date_col' => null,
			'varchar_col' => 'required_unique_' . time(),
			'is_active' => 0,
			'json_data' => '{}',
		];

		$inserted = $wpdb->insert( $table_name, $data );
		$this->assertNotFalse( $inserted );

		$insert_id = $wpdb->insert_id;

		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Verify NULL values
		$this->assertNull( $result['smallint_col'] );
		$this->assertNull( $result['decimal_col'] );
		$this->assertNull( $result['text_col'] );
		$this->assertNull( $result['date_col'] );
	}

	/**
	 * Test default values.
	 *
	 * @test
	 */
	public function should_use_default_values() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert minimal data to test defaults
		$data = [
			'varchar_col' => 'test_defaults_' . time(),
			'json_data' => '{}',
		];

		$inserted = $wpdb->insert( $table_name, $data );
		$this->assertNotFalse( $inserted );

		$insert_id = $wpdb->insert_id;

		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Verify default values
		$this->assertEquals( 0, (int) $result['tinyint_col'] );
		$this->assertEquals( 100, (int) $result['mediumint_col'] );
		$this->assertEquals( 0, (float) $result['float_col'] );
		$this->assertEquals( 'DEFAULT', trim( $result['char_col'] ) );
		$this->assertEquals( 1, (int) $result['is_active'] );
		$this->assertEquals( '0000-00-00 00:00:00', $result['datetime_col'] );
	}

	/**
	 * Test unique constraints.
	 *
	 * @test
	 */
	public function should_enforce_unique_constraints() {
		global $wpdb;

		$table = $this->get_indexed_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert first record
		$data1 = [
			'unique_email' => 'test@example.com',
			'indexed_slug' => 'test-slug',
			'user_id' => 1,
			'category' => 'category1',
			'tag' => 'tag1',
			'title' => 'Test Title',
			'description' => 'Test Description',
			'searchable_content' => 'Searchable content here',
		];

		$inserted1 = $wpdb->insert( $table_name, $data1 );
		$this->assertNotFalse( $inserted1 );

		// Try to insert duplicate unique_email
		$data2 = $data1;
		$data2['indexed_slug'] = 'different-slug';

		$wpdb->suppress_errors( true );
		$inserted2 = $wpdb->insert( $table_name, $data2 );
		$wpdb->suppress_errors( false );

		$this->assertFalse( $inserted2 );

		// Try to insert duplicate composite unique key
		$data3 = [
			'unique_email' => 'another@example.com',
			'indexed_slug' => 'another-slug',
			'user_id' => 1,
			'category' => 'category1',
			'tag' => 'tag2',
			'title' => 'Another Title',
			'description' => 'Another Description',
			'searchable_content' => 'More searchable content',
		];

		$wpdb->suppress_errors( true );
		$inserted3 = $wpdb->insert( $table_name, $data3 );
		$wpdb->suppress_errors( false );

		$this->assertFalse( $inserted3 );
	}

	/**
	 * Test composite index queries.
	 *
	 * @test
	 */
	public function should_use_composite_indexes_efficiently() {
		global $wpdb;

		$table = $this->get_indexed_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert test data
		for ( $i = 1; $i <= 10; $i++ ) {
			$data = [
				'unique_email' => "user$i@example.com",
				'indexed_slug' => "slug-$i",
				'user_id' => $i,
				'category' => 'category' . ( $i % 3 ),
				'tag' => 'tag' . ( $i % 2 ),
				'title' => "Title $i",
				'description' => "Description $i",
				'searchable_content' => "Content $i",
				'status' => $i % 2,
				'published_at' => date( 'Y-m-d H:i:s', strtotime( "+$i days" ) ),
			];
			$wpdb->insert( $table_name, $data );
		}

		// Query using composite index
		$query = $wpdb->prepare(
			"SELECT * FROM $table_name WHERE category = %s AND tag = %s",
			'category0',
			'tag0'
		);

		$results = $wpdb->get_results( $query, ARRAY_A );
		$this->assertNotEmpty( $results );

		// Query using another composite index
		$query2 = $wpdb->prepare(
			"SELECT * FROM $table_name WHERE status = %d AND published_at > %s",
			1,
			date( 'Y-m-d H:i:s' )
		);

		$results2 = $wpdb->get_results( $query2, ARRAY_A );
		$this->assertNotEmpty( $results2 );
	}

	/**
	 * Test timestamp auto-update functionality (MySQL 5.5 compatible).
	 *
	 * @test
	 */
	public function should_auto_update_timestamp_column() {
		global $wpdb;

		$table = $this->get_timestamp_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert initial data
		$data = [
			'title' => 'Test Title',
			'created_date' => '2024-01-01 10:00:00',
		];

		$wpdb->insert( $table_name, $data );
		$insert_id = $wpdb->insert_id;

		// Get initial timestamps
		$initial = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		$initial_timestamp = $initial['timestamp_col'];

		// Wait a moment
		sleep( 1 );

		// Update the record
		$wpdb->update(
			$table_name,
			[ 'title' => 'Updated Title' ],
			[ 'id' => $insert_id ]
		);

		// Get updated timestamps
		$updated = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Verify timestamp was updated
		$this->assertNotEquals( $initial_timestamp, $updated['timestamp_col'] );
	}

	/**
	 * Test Created_At special column.
	 *
	 * @test
	 */
	public function should_handle_created_at_column() {
		global $wpdb;

		$table = $this->get_created_at_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert data
		$data = [
			'name' => 'Test Name',
		];

		$wpdb->insert( $table_name, $data );
		$insert_id = $wpdb->insert_id;

		// Get the record
		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Verify created_at was set
		$this->assertNotNull( $result['created_at'] );
		$this->assertNotEquals( '0000-00-00 00:00:00', $result['created_at'] );

		$created_at_initial = $result['created_at'];

		// Wait and update
		sleep( 1 );

		$wpdb->update(
			$table_name,
			[ 'name' => 'Updated Name' ],
			[ 'id' => $insert_id ]
		);

		// Get updated record
		$updated = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Created_at should not change on update
		$this->assertEquals( $created_at_initial, $updated['created_at'] );
	}

	/**
	 * Test Updated_At special column.
	 *
	 * @test
	 */
	public function should_handle_updated_at() {
		global $wpdb;

		$table = $this->get_updated_at_table();
		Register::table( $table );

		$table_name = $table->table_name();

		// Insert data
		$data = [
			'content' => 'Initial Content',
		];

		$wpdb->insert( $table_name, $data );
		$insert_id = $wpdb->insert_id;

		// Get the initial record
		$initial = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Both columns should have values
		$this->assertNull( $initial['updated_at'] );

		$initial_updated_at = $initial['updated_at'];

		$wpdb->update(
			$table_name,
			[ 'content' => 'Updated Content' ],
			[ 'id' => $insert_id ]
		);

		// Get updated record
		$updated = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		// Both should be updated
		$this->assertNotEquals( $initial_updated_at, $updated['updated_at'] );
	}

	/**
	 * Test table creation for all timestamp tables.
	 *
	 * @test
	 */
	public function should_create_timestamp_tables() {
		$timestamp_table = $this->get_timestamp_table();
		$created_at_table = $this->get_created_at_table();
		$updated_at_table = $this->get_updated_at_table();

		Register::table( $timestamp_table );
		Register::table( $created_at_table );
		Register::table( $updated_at_table );

		$this->assertTrue( $timestamp_table->exists() );
		$this->assertTrue( $created_at_table->exists() );
		$this->assertTrue( $updated_at_table->exists() );
	}

	/**
	 * Test data type transformations through Schema API methods.
	 *
	 * @test
	 */
	public function should_transform_data_types_through_api() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		// Insert test data directly
		$data = [
			'tinyint_col' => 1,
			'smallint_col' => 100,
			'mediumint_col' => 1000,
			'int_col' => 123456,
			'bigint_col' => '9876543210',
			'float_col' => 99.99,
			'decimal_col' => '456.789',
			'double_col' => 3.14159265359,
			'char_col' => 'CHAR',
			'varchar_col' => 'api_test_' . time(),
			'tinytext_col' => 'Tiny API text',
			'text_col' => 'Regular API text content',
			'mediumtext_col' => 'Medium API text content',
			'longtext_col' => 'Long API text content',
			'date_col' => '2024-03-15',
			'datetime_col' => '2024-03-15 10:30:00',
			'is_active' => 1,
			'json_data' => json_encode( [ 'api' => true, 'version' => 3, 'features' => [ 'type_safety', 'transformations' ] ] ),
		];

		$wpdb->insert( $table::table_name(), $data );
		$insert_id = $wpdb->insert_id;

		// Test get_by_id method
		$result = $table::get_by_id( $insert_id );

		$this->assertNotNull( $result );
		$this->assertIsArray( $result );

		// Verify integer types are properly cast
		$this->assertIsInt( $result['id'] );
		$this->assertIsInt( $result['tinyint_col'] );
		$this->assertIsInt( $result['smallint_col'] );
		$this->assertIsInt( $result['mediumint_col'] );
		$this->assertIsInt( $result['int_col'] );
		$this->assertEquals( 123456, $result['int_col'] );

		// Bigint remains as string to avoid precision loss
		$this->assertEquals( '9876543210', $result['bigint_col'] );

		// Verify float types
		$this->assertIsFloat( $result['float_col'] );
		$this->assertIsFloat( $result['decimal_col'] );
		$this->assertIsFloat( $result['double_col'] );
		$this->assertEqualsWithDelta( 99.99, $result['float_col'], 0.01 );
		$this->assertEqualsWithDelta( 456.789, $result['decimal_col'], 0.001 );

		// Verify string types
		$this->assertIsString( $result['char_col'] );
		$this->assertIsString( $result['varchar_col'] );
		$this->assertIsString( $result['tinytext_col'] );
		$this->assertIsString( $result['text_col'] );

		// Verify boolean transformation
		$this->assertIsBool( $result['is_active'] );
		$this->assertTrue( $result['is_active'] );

		// Verify JSON transformation
		$this->assertIsArray( $result['json_data'] );
		$this->assertTrue( $result['json_data']['api'] );
		$this->assertEquals( 3, $result['json_data']['version'] );
		$this->assertContains( 'type_safety', $result['json_data']['features'] );
	}

	/**
	 * Test get_first_by method with type transformations.
	 *
	 * @test
	 */
	public function should_transform_types_with_get_first_by() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		// Insert multiple records
		$unique_varchar = 'first_by_test_' . time();
		$data = [
			'varchar_col' => $unique_varchar,
			'int_col' => 42,
			'float_col' => 3.14,
			'is_active' => 0,
			'json_data' => json_encode( [ 'method' => 'get_first_by', 'test' => true ] ),
		];

		$wpdb->insert( $table::table_name(), $data );

		// Test get_first_by
		$result = $table::get_first_by( 'varchar_col', $unique_varchar );

		$this->assertNotNull( $result );

		// Verify type transformations
		$this->assertIsInt( $result['int_col'] );
		$this->assertEquals( 42, $result['int_col'] );

		$this->assertIsFloat( $result['float_col'] );
		$this->assertEqualsWithDelta( 3.14, $result['float_col'], 0.01 );

		$this->assertIsBool( $result['is_active'] );
		$this->assertFalse( $result['is_active'] );

		$this->assertIsArray( $result['json_data'] );
		$this->assertEquals( 'get_first_by', $result['json_data']['method'] );
		$this->assertTrue( $result['json_data']['test'] );
	}

	/**
	 * Test get_all_by method with type transformations.
	 *
	 * @test
	 */
	public function should_transform_types_with_get_all_by() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		// Insert multiple records with same int_col value
		$shared_int_value = 999;
		for ( $i = 1; $i <= 3; $i++ ) {
			$data = [
				'varchar_col' => 'get_all_test_' . $i . '_' . time(),
				'int_col' => $shared_int_value,
				'float_col' => $i * 1.5,
				'is_active' => $i % 2,
				'json_data' => json_encode( [ 'index' => $i, 'batch' => 'test' ] ),
			];
			$wpdb->insert( $table::table_name(), $data );
		}

		// Test get_all_by
		$results = $table::get_all_by( 'int_col', $shared_int_value );

		$this->assertIsArray( $results );
		$this->assertGreaterThanOrEqual( 3, count( $results ) );

		foreach ( $results as $index => $result ) {
			// Verify each result has proper type transformations
			$this->assertIsInt( $result['int_col'] );
			$this->assertEquals( $shared_int_value, $result['int_col'] );

			$this->assertIsFloat( $result['float_col'] );

			$this->assertIsBool( $result['is_active'] );

			$this->assertIsArray( $result['json_data'] );
			$this->assertArrayHasKey( 'index', $result['json_data'] );
			$this->assertEquals( 'test', $result['json_data']['batch'] );
		}
	}

	/**
	 * Test paginate method with type transformations.
	 *
	 * @test
	 */
	public function should_transform_types_with_paginate() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		// Insert test records for pagination
		$base_time = time();
		for ( $i = 1; $i <= 5; $i++ ) {
			$data = [
				'varchar_col' => 'paginate_test_' . $i . '_' . $base_time,
				'int_col' => $i * 10,
				'float_col' => $i * 2.5,
				'is_active' => $i <= 3 ? 1 : 0,
				'json_data' => json_encode( [ 'page_item' => $i, 'total' => 5 ] ),
			];
			$wpdb->insert( $table::table_name(), $data );
		}

		// Test paginate with search and filters
		$args = [
			'term' => 'paginate_test',
			[
				'column' => 'is_active',
				'value' => 1,
				'operator' => '=',
			],
		];

		$results = $table::paginate( $args, 10, 1, [ '*' ], '', '', [], ARRAY_A );

		$this->assertIsArray( $results );
		$this->assertNotEmpty( $results );

		foreach ( $results as $result ) {
			// Verify type transformations in paginated results
			$this->assertIsInt( $result['int_col'] );
			$this->assertIsFloat( $result['float_col'] );
			$this->assertIsBool( $result['is_active'] );
			$this->assertTrue( $result['is_active'] ); // We filtered for active only

			$this->assertIsArray( $result['json_data'] );
			$this->assertArrayHasKey( 'page_item', $result['json_data'] );
			$this->assertEquals( 5, $result['json_data']['total'] );
		}
	}

	/**
	 * Test fetch_all generator with type transformations.
	 *
	 * @test
	 */
	public function should_transform_types_with_fetch_all() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		// Insert a few test records
		$base_time = time();
		for ( $i = 1; $i <= 2; $i++ ) {
			$data = [
				'varchar_col' => 'fetch_all_test_' . $i . '_' . $base_time,
				'int_col' => $i * 100,
				'float_col' => $i * 0.5,
				'is_active' => 1,
				'json_data' => json_encode( [ 'fetched' => true, 'order' => $i ] ),
			];
			$wpdb->insert( $table::table_name(), $data );
		}

		// Test fetch_all_where
		$count = 0;

		foreach ( $table::get_all_by( 'varchar_col', 'fetch_all_test_%' . $base_time, 'LIKE' ) as $row ) {
			// Note: fetch_all returns raw data without transformation
			// We need to manually transform it
			$transformed = $table::transform_from_array( $row );

			// Verify the transformation worked
			$this->assertIsInt( $transformed['int_col'] );
			$this->assertIsBool( $transformed['is_active'] );
			$this->assertIsArray( $transformed['json_data'] );
			$this->assertTrue( $transformed['json_data']['fetched'] );

			$count++;
		}

		$this->assertGreaterThanOrEqual( 2, $count );
	}

	/**
	 * Test nullable column handling through API.
	 *
	 * @test
	 */
	public function should_handle_null_values_in_api() {
		global $wpdb;

		$table = $this->get_comprehensive_table();
		Register::table( $table );

		// Insert record with NULL values
		$unique_varchar = 'null_test_' . time();
		$data = [
			'varchar_col' => $unique_varchar,
			'smallint_col' => null,
			'decimal_col' => null,
			'text_col' => null,
			'date_col' => null,
			'json_data' => '{}',
		];

		$wpdb->insert( $table::table_name(), $data );

		// Retrieve through API
		$result = $table::get_first_by( 'varchar_col', $unique_varchar );

		$this->assertNotNull( $result );

		// Verify NULL values are preserved
		$this->assertNull( $result['smallint_col'] );
		$this->assertNull( $result['decimal_col'] );
		$this->assertNull( $result['text_col'] );
		$this->assertNull( $result['date_col'] );

		// Non-nullable fields should have their defaults
		$this->assertIsInt( $result['tinyint_col'] );
		$this->assertEquals( 0, $result['tinyint_col'] );
	}

	/**
	 * Test special column types through API.
	 *
	 * @test
	 */
	public function should_handle_special_columns_through_api() {
		global $wpdb;

		$table = $this->get_updated_at_table();
		Register::table( $table );

		// Insert record
		$content = 'Special columns test ' . time();
		$table::insert( [
			'content' => $content,
		] );

		// Retrieve through API
		$result = $table::get_first_by( 'content', $content );

		$this->assertNotNull( $result );
		$this->assertEquals( $content, $result['content'] );

		// Updated_at should be null on insert
		$this->assertNull( $result['updated_at'] );

		// Update the record
		sleep( 1 );
		$wpdb->update(
			$table::table_name(),
			[ 'content' => $content . ' updated' ],
			[ 'id' => $result['id'] ]
		);

		// Retrieve again
		$updated = $table::get_by_id( $result['id'] );

		// Now updated_at should have a value
		$this->assertInstanceOf( DateTime::class, $updated['updated_at'] );
	}
}
