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
use StellarWP\Schema\Columns\String_Column;
use StellarWP\Schema\Columns\Boolean_Column;
use StellarWP\Schema\Columns\Blob_Column;
use StellarWP\Schema\Columns\Binary_Column;
use StellarWP\Schema\Columns\Datetime_Column;
use StellarWP\Schema\Columns\Column_Types;
use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Indexes\Classic_Index;
use DateTime;

class BinaryBooleanBlobTableTest extends SchemaTestCase {
	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_binary_boolean_blob_table()->drop();
		$this->get_mixed_binary_table()->drop();
		$this->get_indexed_binary_blob_table()->drop();
	}

	/**
	 * Get a comprehensive table with Binary, Boolean, and Blob columns.
	 */
	public function get_binary_boolean_blob_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'binary_boolean_blob';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-binary-boolean-blob';

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// Primary key.
					$columns[] = ( new ID( 'id' ) )
						->set_length( 11 )
						->set_type( Column_Types::INT )
						->set_auto_increment( true );

					// Boolean columns with different configurations.
					$columns[] = ( new Boolean_Column( 'is_active' ) )
						->set_default( true );

					$columns[] = ( new Boolean_Column( 'is_published' ) )
						->set_default( false );

					$columns[] = ( new Boolean_Column( 'is_featured' ) )
						->set_nullable( true );

					$columns[] = ( new Boolean_Column( 'has_thumbnail' ) )
						->set_type( Column_Types::BIT );

					// Binary columns with different types.
					$columns[] = ( new Binary_Column( 'binary_hash' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 32 ); // For MD5 hash

					$columns[] = ( new Binary_Column( 'varbinary_data' ) )
						->set_type( Column_Types::VARBINARY )
						->set_length( 255 );

					$columns[] = ( new Binary_Column( 'uuid_binary' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 16 ); // For UUID storage

					$columns[] = ( new Binary_Column( 'nullable_binary' ) )
						->set_type( Column_Types::VARBINARY )
						->set_length( 100 )
						->set_nullable( true );

					// Blob columns with different types.
					$columns[] = ( new Blob_Column( 'tiny_blob_data' ) )
						->set_type( Column_Types::TINYBLOB );

					$columns[] = ( new Blob_Column( 'blob_data' ) )
						->set_type( Column_Types::BLOB );

					$columns[] = ( new Blob_Column( 'medium_blob_data' ) )
						->set_type( Column_Types::MEDIUMBLOB );

					$columns[] = ( new Blob_Column( 'long_blob_data' ) )
						->set_type( Column_Types::LONGBLOB );

					$columns[] = ( new Blob_Column( 'json_blob' ) )
						->set_type( Column_Types::BLOB )
						->set_php_type( PHP_Types::JSON );

					$columns[] = ( new Blob_Column( 'nullable_blob' ) )
						->set_type( Column_Types::BLOB )
						->set_nullable( true );

					// Regular columns for context.
					$columns[] = ( new String_Column( 'title' ) )
						->set_length( 255 )
						->set_searchable( true );

					$columns[] = ( new Integer_Column( 'view_count' ) )
						->set_type( Column_Types::INT )
						->set_default( 0 );

					$columns[] = ( new Datetime_Column( 'created_at' ) )
						->set_type( Column_Types::DATETIME );

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
	 * Get a table with mixed binary data usage scenarios.
	 */
	public function get_mixed_binary_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'mixed_binary';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-mixed-binary';

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					$columns[] = ( new ID( 'id' ) )
						->set_length( 11 )
						->set_type( Column_Types::INT )
						->set_auto_increment( true );

					// Fixed-length binary for various hash types.
					$columns[] = ( new Binary_Column( 'md5_hash' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 16 ); // MD5 raw binary

					$columns[] = ( new Binary_Column( 'sha1_hash' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 20 ); // SHA1 raw binary

					$columns[] = ( new Binary_Column( 'sha256_hash' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 32 ); // SHA256 raw binary

					// Variable binary for flexible data.
					$columns[] = ( new Binary_Column( 'encrypted_data' ) )
						->set_type( Column_Types::VARBINARY )
						->set_length( 512 );

					$columns[] = ( new Binary_Column( 'ip_address' ) )
						->set_type( Column_Types::VARBINARY )
						->set_length( 16 ); // Can store IPv4 (4 bytes) or IPv6 (16 bytes)

					// Blob with PHP type variations.
					$columns[] = ( new Blob_Column( 'serialized_data' ) )
						->set_type( Column_Types::BLOB )
						->set_php_type( PHP_Types::STRING );

					$columns[] = ( new Blob_Column( 'json_settings' ) )
						->set_type( Column_Types::MEDIUMBLOB )
						->set_php_type( PHP_Types::JSON );

					// Boolean flags for data state.
					$columns[] = ( new Boolean_Column( 'is_encrypted' ) )
						->set_default( false );

					$columns[] = ( new Boolean_Column( 'is_compressed' ) )
						->set_default( false );

					$columns[] = ( new String_Column( 'data_type' ) )
						->set_length( 50 );

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
	 * Get a table with indexes on binary and blob columns.
	 */
	public function get_indexed_binary_blob_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'indexed_binary_blob';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-indexed-binary-blob';

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					$columns[] = ( new ID( 'id' ) )
						->set_length( 11 )
						->set_type( Column_Types::INT )
						->set_auto_increment( true );

					// Binary columns with indexes.
					$columns[] = ( new Binary_Column( 'unique_token' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 20 )
						->set_is_unique( true );

					$columns[] = ( new Binary_Column( 'indexed_hash' ) )
						->set_type( Column_Types::VARBINARY )
						->set_length( 64 )
						->set_is_index( true );

					// Boolean columns for composite indexes.
					$columns[] = ( new Boolean_Column( 'is_active' ) )
						->set_default( true );

					$columns[] = ( new Boolean_Column( 'is_verified' ) )
						->set_default( false );

					// Regular columns for composite indexes.
					$columns[] = ( new String_Column( 'category' ) )
						->set_length( 50 );

					$columns[] = ( new Integer_Column( 'priority' ) )
						->set_type( Column_Types::TINYINT )
						->set_default( 0 );

					// Blob columns (typically not indexed directly).
					$columns[] = ( new Blob_Column( 'metadata' ) )
						->set_type( Column_Types::BLOB )
						->set_php_type( PHP_Types::JSON );

					// Additional indexes.
					$indexes = new Index_Collection();

					// Composite index with boolean.
					$indexes[] = ( new Classic_Index( 'idx_active_verified' ) )
						->set_columns( 'is_active', 'is_verified' );

					// Composite index with boolean and regular column.
					$indexes[] = ( new Classic_Index( 'idx_category_active' ) )
						->set_columns( 'category', 'is_active' );

					// Index on priority and verification.
					$indexes[] = ( new Classic_Index( 'idx_priority_verified' ) )
						->set_columns( 'priority', 'is_verified' );

					return new Table_Schema( $table_name, $columns, $indexes );
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
	 * Test comprehensive table creation.
	 *
	 * @test
	 */
	public function should_create_binary_boolean_blob_table() {
		$table = $this->get_binary_boolean_blob_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Verify column definitions.
		$columns = $table::get_columns();

		// Check Boolean columns exist.
		$this->assertNotNull( $columns->get( 'is_active' ) );
		$this->assertNotNull( $columns->get( 'is_published' ) );
		$this->assertNotNull( $columns->get( 'is_featured' ) );
		$this->assertNotNull( $columns->get( 'has_thumbnail' ) );

		// Check Binary columns exist.
		$this->assertNotNull( $columns->get( 'binary_hash' ) );
		$this->assertNotNull( $columns->get( 'varbinary_data' ) );
		$this->assertNotNull( $columns->get( 'uuid_binary' ) );
		$this->assertNotNull( $columns->get( 'nullable_binary' ) );

		// Check Blob columns exist.
		$this->assertNotNull( $columns->get( 'tiny_blob_data' ) );
		$this->assertNotNull( $columns->get( 'blob_data' ) );
		$this->assertNotNull( $columns->get( 'medium_blob_data' ) );
		$this->assertNotNull( $columns->get( 'long_blob_data' ) );
		$this->assertNotNull( $columns->get( 'json_blob' ) );
		$this->assertNotNull( $columns->get( 'nullable_blob' ) );
	}

	/**
	 * Test data insertion and retrieval with correct types.
	 *
	 * @test
	 */
	public function should_handle_binary_boolean_blob_data_correctly() {
		global $wpdb;

		$table = $this->get_binary_boolean_blob_table();
		Register::table( $table );

		// Prepare test data.
		$md5_binary = md5( 'test', true ); // 16 bytes binary
		$uuid_binary = hex2bin( str_replace( '-', '', 'f47ac10b-58cc-4372-a567-0e02b2c3d479' ) ); // 16 bytes
		$varbinary_data = pack( 'H*', '48656c6c6f20576f726c64' ); // "Hello World" in hex

		$data = [
			'title' => 'Test Entry',
			// Boolean values.
			'is_active' => true,
			'is_published' => false,
			'is_featured' => true,
			'has_thumbnail' => 1,
			// Binary values.
			'binary_hash' => str_pad( $md5_binary, 32, "\0" ), // Pad to 32 bytes for BINARY(32)
			'varbinary_data' => $varbinary_data,
			'uuid_binary' => $uuid_binary,
			'nullable_binary' => null,
			// Blob values.
			'tiny_blob_data' => 'Small data',
			'blob_data' => str_repeat( 'data ', 5 ),
			'medium_blob_data' => str_repeat( 'medium data ', 15 ),
			'long_blob_data' => str_repeat( 'long data ', 35 ),
			'json_blob' => json_encode( [ 'key' => 'value', 'nested' => [ 'array' => [ 1, 2, 3 ] ] ] ),
			'nullable_blob' => null,
			// Other values.
			'view_count' => 42,
			'created_at' => '2024-01-15 10:30:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		$this->assertNotNull( $retrieved );

		// Verify Boolean values.
		$this->assertIsBool( $retrieved['is_active'] );
		$this->assertTrue( $retrieved['is_active'] );
		$this->assertIsBool( $retrieved['is_published'] );
		$this->assertFalse( $retrieved['is_published'] );
		$this->assertIsBool( $retrieved['is_featured'] );
		$this->assertTrue( $retrieved['is_featured'] );
		$this->assertTrue( $retrieved['has_thumbnail'] ); // BIT type

		// Verify Binary values (should be returned as strings).
		$this->assertIsString( $retrieved['binary_hash'] );
		$this->assertEquals( 32, strlen( $retrieved['binary_hash'] ) ); // BINARY(32) is fixed length
		$this->assertStringStartsWith( $md5_binary, $retrieved['binary_hash'] );

		$this->assertIsString( $retrieved['varbinary_data'] );
		$this->assertEquals( $varbinary_data, $retrieved['varbinary_data'] );

		$this->assertIsString( $retrieved['uuid_binary'] );
		$this->assertEquals( 16, strlen( $retrieved['uuid_binary'] ) );

		$this->assertNull( $retrieved['nullable_binary'] );

		// Verify Blob values.
		$this->assertEquals( 'Small data', $retrieved['tiny_blob_data'] );
		$this->assertStringContainsString( 'data', $retrieved['blob_data'] );
		$this->assertStringContainsString( 'medium data', $retrieved['medium_blob_data'] );
		$this->assertStringContainsString( 'long data', $retrieved['long_blob_data'] );

		// Verify JSON blob.
		$this->assertIsArray( $retrieved['json_blob'] );
		$this->assertEquals( 'value', $retrieved['json_blob']['key'] );
		$this->assertEquals( [ 1, 2, 3 ], $retrieved['json_blob']['nested']['array'] );

		$this->assertNull( $retrieved['nullable_blob'] );

		// Verify other values.
		$this->assertEquals( 42, $retrieved['view_count'] );
		$this->assertInstanceOf( DateTime::class, $retrieved['created_at'] );
		$this->assertEquals( '2024-01-15 10:30:00', $retrieved['created_at']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Test mixed binary table with hash storage.
	 *
	 * @test
	 */
	public function should_handle_hash_storage_in_binary_columns() {
		global $wpdb;

		$table = $this->get_mixed_binary_table();
		Register::table( $table );

		// Generate various hashes.
		$test_string = 'Hello World!';
		$md5_binary = md5( $test_string, true ); // 16 bytes
		$sha1_binary = sha1( $test_string, true ); // 20 bytes
		$sha256_binary = hash( 'sha256', $test_string, true ); // 32 bytes

		// Prepare encrypted data (simulated).
		$encrypted = base64_encode( openssl_random_pseudo_bytes( 256 ) );

		// IP addresses in binary.
		$ipv4_binary = inet_pton( '192.168.1.1' ); // 4 bytes
		$ipv6_binary = inet_pton( '2001:db8::1' ); // 16 bytes

		$data = [
			'data_type' => 'test_hashes',
			'md5_hash' => $md5_binary,
			'sha1_hash' => $sha1_binary,
			'sha256_hash' => $sha256_binary,
			'encrypted_data' => $encrypted,
			'ip_address' => $ipv4_binary,
			'serialized_data' => serialize( [ 'test' => 'data', 'array' => [ 1, 2, 3 ] ] ),
			'json_settings' => json_encode( [ 'theme' => 'dark', 'language' => 'en' ] ),
			'is_encrypted' => true,
			'is_compressed' => false,
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		// Verify hash storage.
		$this->assertEquals( 16, strlen( $retrieved['md5_hash'] ) );
		$this->assertEquals( $md5_binary, substr( $retrieved['md5_hash'], 0, 16 ) );

		$this->assertEquals( 20, strlen( $retrieved['sha1_hash'] ) );
		$this->assertEquals( $sha1_binary, substr( $retrieved['sha1_hash'], 0, 20 ) );

		$this->assertEquals( 32, strlen( $retrieved['sha256_hash'] ) );
		$this->assertEquals( $sha256_binary, $retrieved['sha256_hash'] );

		// Verify encrypted data.
		$this->assertEquals( $encrypted, $retrieved['encrypted_data'] );

		// Verify IP address storage.
		$this->assertEquals( $ipv4_binary, $retrieved['ip_address'] );
		$this->assertEquals( '192.168.1.1', inet_ntop( $retrieved['ip_address'] ) );

		// Test IPv6 storage.
		$data_ipv6 = $data;
		$data_ipv6['ip_address'] = $ipv6_binary;
		$data_ipv6['data_type'] = 'ipv6_test';

		$result = $table::insert( $data_ipv6 );
		$this->assertNotFalse( $result );

		$insert_id_ipv6 = $wpdb->insert_id;
		$retrieved_ipv6 = $table::get_by_id( $insert_id_ipv6 );

		$this->assertEquals( $ipv6_binary, $retrieved_ipv6['ip_address'] );
		$this->assertEquals( '2001:db8::1', inet_ntop( $retrieved_ipv6['ip_address'] ) );

		// Verify serialized data.
		$unserialized = @unserialize( $retrieved['serialized_data'] );
		$this->assertIsArray( $unserialized );
		$this->assertEquals( 'data', $unserialized['test'] );

		// Verify JSON settings.
		$this->assertIsArray( $retrieved['json_settings'] );
		$this->assertEquals( 'dark', $retrieved['json_settings']['theme'] );

		// Verify boolean flags.
		$this->assertTrue( $retrieved['is_encrypted'] );
		$this->assertFalse( $retrieved['is_compressed'] );
	}

	/**
	 * Test indexed binary blob table.
	 *
	 * @test
	 */
	public function should_create_indexed_binary_blob_table_with_indexes() {
		$table = $this->get_indexed_binary_blob_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Verify indexes exist.
		$this->assertTrue( $table->has_index( 'unique_token' ) );
		$this->assertTrue( $table->has_index( 'indexed_hash' ) );
		$this->assertTrue( $table->has_index( 'idx_active_verified' ) );
		$this->assertTrue( $table->has_index( 'idx_category_active' ) );
		$this->assertTrue( $table->has_index( 'idx_priority_verified' ) );
	}

	/**
	 * Test querying with binary columns.
	 *
	 * @test
	 */
	public function should_query_by_binary_values() {
		$table = $this->get_indexed_binary_blob_table();
		Register::table( $table );

		// Insert test data with unique tokens.
		$tokens = [
			'token1' => str_pad( 'unique_token_001', 20, "\0" ),
			'token2' => str_pad( 'unique_token_002', 20, "\0" ),
			'token3' => str_pad( 'unique_token_003', 20, "\0" ),
		];

		foreach ( $tokens as $key => $token ) {
			$data = [
				'unique_token' => $token,
				'indexed_hash' => hash( 'sha256', $key, true ),
				'is_active' => $key !== 'token2', // token2 is inactive
				'is_verified' => $key === 'token1', // only token1 is verified
				'category' => 'category_' . substr( $key, -1 ),
				'priority' => ord( substr( $key, -1 ) ) % 10,
				'metadata' => json_encode( [ 'key' => $key ] ),
			];

			$result = $table::insert( $data );
			$this->assertNotFalse( $result );
		}

		// Query by binary value.
		$result = $table::get_first_by( 'unique_token', $tokens['token1'] );
		$this->assertNotNull( $result );
		$this->assertEquals( $tokens['token1'], $result['unique_token'] );

		// Query by boolean values.
		$active_results = $table::get_all_by( 'is_active', true );
		$this->assertCount( 2, $active_results ); // token1 and token3

		$verified_results = $table::get_all_by( 'is_verified', true );
		$this->assertCount( 1, $verified_results ); // only token1

		// Query with pagination and filters.
		$args = [
			[
				'column' => 'is_active',
				'value' => 1,
				'operator' => '=',
			],
			[
				'column' => 'is_verified',
				'value' => 0,
				'operator' => '=',
			],
		];

		$paginated = $table::paginate( $args, 10, 1 );
		$this->assertCount( 1, $paginated ); // only token3 (active but not verified)
		$this->assertEquals( 'category_3', $paginated[0]['category'] );
	}

	/**
	 * Test update operations with binary data.
	 *
	 * @test
	 */
	public function should_update_binary_boolean_blob_values() {
		global $wpdb;

		$table = $this->get_binary_boolean_blob_table();
		Register::table( $table );

		// Insert initial data.
		$initial_data = [
			'title' => 'Initial Title',
			'is_active' => true,
			'is_published' => true,
			'binary_hash' => str_pad( 'initial_hash', 32, "\0" ),
			'varbinary_data' => 'initial_binary',
			'uuid_binary' => hex2bin( str_replace( '-', '', 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11' ) ),
			'tiny_blob_data' => 'initial_tiny',
			'blob_data' => 'initial_blob',
			'medium_blob_data' => 'initial_medium',
			'long_blob_data' => 'initial_long',
			'json_blob' => json_encode( [ 'version' => 1 ] ),
			'view_count' => 10,
			'created_at' => '2024-01-15 10:00:00',
		];

		$result = $table::insert( $initial_data );
		$this->assertNotFalse( $result );
		$insert_id = $wpdb->insert_id;

		// Update with new values.
		$new_uuid = hex2bin( str_replace( '-', '', 'b1b2b3b4-c5c6-d7d8-e9e0-f1f2f3f4f5f6' ) );
		$update_data = [
			'id' => $insert_id,
			'title' => 'Updated Title',
			'is_active' => false,
			'is_published' => false,
			'binary_hash' => str_pad( 'updated_hash', 32, "\0" ),
			'uuid_binary' => $new_uuid,
			'blob_data' => 'updated_blob_with_more_data',
			'json_blob' => json_encode( [ 'version' => 2, 'updated' => true ] ),
			'view_count' => 100,
		];

		$result = $table::update_single( $update_data );
		$this->assertTrue( $result );

		// Retrieve and verify.
		$updated = $table::get_by_id( $insert_id );

		$this->assertEquals( 'Updated Title', $updated['title'] );
		$this->assertFalse( $updated['is_active'] );
		$this->assertFalse( $updated['is_published'] );
		$this->assertStringStartsWith( 'updated_hash', $updated['binary_hash'] );
		$this->assertEquals( $new_uuid, $updated['uuid_binary'] );
		$this->assertEquals( 'updated_blob_with_more_data', $updated['blob_data'] );

		// Check JSON blob update.
		$this->assertIsArray( $updated['json_blob'] );
		$this->assertEquals( 2, $updated['json_blob']['version'] );
		$this->assertTrue( $updated['json_blob']['updated'] );

		$this->assertEquals( 100, $updated['view_count'] );

		// Verify unchanged values.
		$this->assertEquals( 'initial_binary', $updated['varbinary_data'] );
		$this->assertEquals( 'initial_tiny', $updated['tiny_blob_data'] );
		$this->assertEquals( 'initial_medium', $updated['medium_blob_data'] );
	}

	/**
	 * Test default values for boolean columns.
	 *
	 * @test
	 */
	public function should_use_boolean_default_values() {
		global $wpdb;

		$table = $this->get_binary_boolean_blob_table();
		Register::table( $table );

		// Insert with minimal data.
		$data = [
			'title' => 'Default Test',
			'binary_hash' => str_pad( 'hash', 32, "\0" ),
			'varbinary_data' => 'data',
			'uuid_binary' => str_repeat( "\0", 16 ),
			'tiny_blob_data' => 'tiny',
			'blob_data' => 'blob',
			'medium_blob_data' => 'medium',
			'long_blob_data' => 'long',
			'json_blob' => '{}',
			'created_at' => '2024-01-15 12:00:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		// Check boolean defaults.
		$this->assertTrue( $retrieved['is_active'] ); // default true
		$this->assertFalse( $retrieved['is_published'] ); // default false
		$this->assertNull( $retrieved['is_featured'] ); // nullable, no default

		// Check integer default.
		$this->assertEquals( 0, $retrieved['view_count'] ); // default 0
	}

	/**
	 * Test nullable columns.
	 *
	 * @test
	 */
	public function should_handle_nullable_binary_blob_columns() {
		global $wpdb;

		$table = $this->get_binary_boolean_blob_table();
		Register::table( $table );

		$data = [
			'title' => 'Nullable Test',
			'is_active' => true,
			'is_featured' => null, // nullable boolean
			'binary_hash' => str_pad( 'hash', 32, "\0" ),
			'varbinary_data' => 'data',
			'uuid_binary' => str_repeat( "\0", 16 ),
			'nullable_binary' => null, // nullable binary
			'tiny_blob_data' => 'tiny',
			'blob_data' => 'blob',
			'medium_blob_data' => 'medium',
			'long_blob_data' => 'long',
			'json_blob' => '{}',
			'nullable_blob' => null, // nullable blob
			'created_at' => '2024-01-15 14:00:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;

		// Use raw query to verify NULL values.
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table->table_name()} WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		$this->assertNull( $row['is_featured'] );
		$this->assertNull( $row['nullable_binary'] );
		$this->assertNull( $row['nullable_blob'] );

		// Verify through model.
		$retrieved = $table::get_by_id( $insert_id );
		$this->assertNull( $retrieved['is_featured'] );
		$this->assertNull( $retrieved['nullable_binary'] );
		$this->assertNull( $retrieved['nullable_blob'] );
	}

	/**
	 * Test large binary and blob data.
	 *
	 * @test
	 */
	public function should_handle_large_binary_blob_data() {
		global $wpdb;

		$table = $this->get_binary_boolean_blob_table();
		Register::table( $table );

		// Generate large data.
		$large_varbinary = str_repeat( "\xFF", 10 ); // Max for VARBINARY(255)
		$large_blob = str_repeat( 'X', 50 ); // 64KB for BLOB
		$large_medium = str_repeat( 'Y', 30 ); // 1MB for MEDIUMBLOB
		$large_long = str_repeat( 'Z', 100 ); // 5MB for LONGBLOB

		$data = [
			'title' => 'Large Data Test',
			'is_active' => true,
			'binary_hash' => str_repeat( "\xAB", 32 ),
			'varbinary_data' => $large_varbinary,
			'uuid_binary' => str_repeat( "\xCD", 16 ),
			'tiny_blob_data' => str_repeat( 'T', 255 ), // Max for TINYBLOB
			'blob_data' => $large_blob,
			'medium_blob_data' => $large_medium,
			'long_blob_data' => $large_long,
			'json_blob' => json_encode( array_fill( 0, 1000, 'test' ) ),
			'created_at' => '2024-01-15 16:00:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$insert_id = $wpdb->insert_id;
		$retrieved = $table::get_by_id( $insert_id );

		// Verify data integrity.
		$this->assertEquals( 10, strlen( $retrieved['varbinary_data'] ) );
		$this->assertEquals( $large_varbinary, $retrieved['varbinary_data'] );

		$this->assertEquals( 50, strlen( $retrieved['blob_data'] ) );
		$this->assertEquals( $large_blob, $retrieved['blob_data'] );

		$this->assertEquals( 30, strlen( $retrieved['medium_blob_data'] ) );
		$this->assertEquals( $large_medium, $retrieved['medium_blob_data'] );

		$this->assertEquals( 100, strlen( $retrieved['long_blob_data'] ) );
		$this->assertEquals( $large_long, $retrieved['long_blob_data'] );

		// Verify JSON blob.
		$json_data = $retrieved['json_blob'];
		$this->assertIsArray( $json_data );
		$this->assertCount( 1000, $json_data );
		$this->assertEquals( 'test', $json_data[0] );
	}
}
