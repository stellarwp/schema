<?php

namespace StellarWP\Schema\Tests\Tables;

use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tables\Contracts\Table;
use StellarWP\Schema\Tables\Table_Schema;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Collections\Index_Collection;
use StellarWP\Schema\Columns\String_Column;
use StellarWP\Schema\Columns\Binary_Column;
use StellarWP\Schema\Columns\Boolean_Column;
use StellarWP\Schema\Columns\Integer_Column;
use StellarWP\Schema\Columns\Text_Column;
use StellarWP\Schema\Columns\Datetime_Column;
use StellarWP\Schema\Columns\Blob_Column;
use StellarWP\Schema\Columns\Column_Types;
use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Indexes\Primary_Key;
use DateTime;
use StellarWP\DB\Database\Exceptions\DatabaseQueryException;

class StringBinaryPrimaryKeyTest extends SchemaTestCase {
	/**
	 * @before
	 */
	public function drop_tables() {
		$this->get_string_primary_key_table()->drop();
		$this->get_binary_primary_key_table()->drop();
		$this->get_composite_string_binary_key_table()->drop();
		$this->get_uuid_primary_key_table()->drop();
		$this->get_email_primary_key_table()->drop();
	}

	/**
	 * Get a table with String column as primary key.
	 */
	public function get_string_primary_key_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'string_pk_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-string-pk';
			protected static $uid_column = 'username';
			protected static $primary_columns = [ 'username' ];

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// String as primary key.
					$columns[] = ( new String_Column( 'username' ) )
						->set_type( Column_Types::VARCHAR )
						->set_length( 50 )
						->set_is_primary_key( true );

					$columns[] = ( new String_Column( 'email' ) )
						->set_length( 255 )
						->set_is_unique( true );

					$columns[] = ( new String_Column( 'full_name' ) )
						->set_length( 100 );

					$columns[] = ( new Boolean_Column( 'is_active' ) )
						->set_default( true );

					$columns[] = ( new Integer_Column( 'login_count' ) )
						->set_type( Column_Types::INT )
						->set_default( 0 );

					$columns[] = ( new Datetime_Column( 'last_login' ) )
						->set_type( Column_Types::DATETIME )
						->set_nullable( true );

					$columns[] = ( new Text_Column( 'bio' ) )
						->set_type( Column_Types::TEXT )
						->set_nullable( true );

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
	 * Get a table with Binary column as primary key.
	 */
	public function get_binary_primary_key_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'binary_pk_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-binary-pk';
			protected static $uid_column = 'binary_id';
			protected static $primary_columns = [ 'binary_id' ];

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// Binary as primary key (e.g., for storing UUIDs in binary format)
					$columns[] = ( new Binary_Column( 'binary_id' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 16 ) // UUID in binary is 16 bytes
						->set_is_primary_key( true );

					$columns[] = ( new String_Column( 'name' ) )
						->set_length( 100 );

					$columns[] = ( new Binary_Column( 'hash_key' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 32 ) // SHA256 hash
						->set_is_unique( true );

					$columns[] = ( new String_Column( 'type' ) )
						->set_length( 50 );

					$columns[] = ( new Blob_Column( 'data' ) )
						->set_type( Column_Types::BLOB )
						->set_nullable( true );

					$columns[] = ( new Integer_Column( 'version' ) )
						->set_type( Column_Types::INT )
						->set_default( 1 );

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
	 * Get a table with composite primary key using String and Binary columns.
	 */
	public function get_composite_string_binary_key_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'composite_pk_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-composite-pk';
			protected static $uid_column = ''; // No single UID column for composite key
			protected static $primary_columns = [ 'tenant_id', 'resource_hash' ];

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();
					$indexes = new Index_Collection();

					// String part of composite primary key.
					$columns[] = ( new String_Column( 'tenant_id' ) )
						->set_type( Column_Types::VARCHAR )
						->set_length( 36 ); // UUID as string

					// Binary part of composite primary key.
					$columns[] = ( new Binary_Column( 'resource_hash' ) )
						->set_type( Column_Types::BINARY )
						->set_length( 20 ); // SHA1 hash

					$columns[] = ( new String_Column( 'resource_type' ) )
						->set_length( 50 );

					$columns[] = ( new String_Column( 'resource_name' ) )
						->set_length( 255 );

					$columns[] = ( new Boolean_Column( 'is_public' ) )
						->set_default( false );

					$columns[] = ( new Integer_Column( 'access_count' ) )
						->set_type( Column_Types::INT )
						->set_default( 0 );

					$columns[] = ( new Datetime_Column( 'last_accessed' ) )
						->set_type( Column_Types::DATETIME )
						->set_nullable( true );

					// Define composite primary key.
					$indexes[] = ( new Primary_Key() )
						->set_columns( 'tenant_id', 'resource_hash' );

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
	 * Get a table with UUID string as primary key.
	 */
	public function get_uuid_primary_key_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'uuid_pk_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-uuid-pk';
			protected static $uid_column = 'uuid';
			protected static $primary_columns = [ 'uuid' ];

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// UUID as string primary key.
					$columns[] = ( new String_Column( 'uuid' ) )
						->set_type( Column_Types::CHAR )
						->set_length( 36 ) // Standard UUID format with hyphens
						->set_is_primary_key( true );

					$columns[] = ( new String_Column( 'entity_type' ) )
						->set_length( 50 );

					$columns[] = ( new Text_Column( 'entity_data' ) )
						->set_type( Column_Types::TEXT )
						->set_php_type( PHP_Types::JSON );

					$columns[] = ( new Boolean_Column( 'is_processed' ) )
						->set_default( false );

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
	 * Get a table with email as primary key.
	 */
	public function get_email_primary_key_table(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '3.0.0';
			protected static $base_table_name = 'email_pk_table';
			protected static $group = 'test_v3';
			protected static $schema_slug = 'test-v3-email-pk';
			protected static $uid_column = 'email';
			protected static $primary_columns = [ 'email' ];

			public static function get_schema_history(): array {
				$table_name = static::table_name( true );
				$callable = function() use ( $table_name ) {
					$columns = new Column_Collection();

					// Email as primary key.
					$columns[] = ( new String_Column( 'email' ) )
						->set_type( Column_Types::VARCHAR )
						->set_length( 255 )
						->set_is_primary_key( true );

					$columns[] = ( new String_Column( 'name' ) )
						->set_length( 100 );

					$columns[] = ( new Boolean_Column( 'is_verified' ) )
						->set_default( false );

					$columns[] = ( new Boolean_Column( 'is_subscribed' ) )
						->set_default( true );

					$columns[] = ( new String_Column( 'subscription_type' ) )
						->set_length( 50 )
						->set_nullable( true );

					$columns[] = ( new Datetime_Column( 'subscribed_at' ) )
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
	 * Test string primary key table creation.
	 *
	 * @test
	 */
	public function should_create_table_with_string_primary_key() {
		$table = $this->get_string_primary_key_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Verify primary key column.
		$columns = $table::get_columns();
		$username_column = $columns->get( 'username' );
		$this->assertNotNull( $username_column );
		$this->assertTrue( $username_column->is_primary_key() );

		// Verify primary key configuration.
		$this->assertEquals( 'username', $table::uid_column() );
		$this->assertEquals( [ 'username' ], $table::primary_columns() );
	}

	/**
	 * Test binary primary key table creation.
	 *
	 * @test
	 */
	public function should_create_table_with_binary_primary_key() {
		$table = $this->get_binary_primary_key_table();

		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Verify primary key column.
		$columns = $table::get_columns();
		$binary_id_column = $columns->get( 'binary_id' );
		$this->assertNotNull( $binary_id_column );
		$this->assertTrue( $binary_id_column->is_primary_key() );

		// Verify primary key configuration.
		$this->assertEquals( 'binary_id', $table::uid_column() );
		$this->assertEquals( [ 'binary_id' ], $table::primary_columns() );
	}

	/**
	 * Test CRUD operations with string primary key.
	 *
	 * @test
	 */
	public function should_perform_crud_with_string_primary_key() {
		$table = $this->get_string_primary_key_table();
		Register::table( $table );

		// Insert.
		$data = [
			'username' => 'john_doe',
			'email' => 'john@example.com',
			'full_name' => 'John Doe',
			'is_active' => true,
			'login_count' => 5,
			'last_login' => '2024-01-15 10:30:00',
			'bio' => 'Software developer',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		// Read by primary key.
		$retrieved = $table::get_by_id( 'john_doe' );
		$this->assertNotNull( $retrieved );
		$this->assertEquals( 'john_doe', $retrieved['username'] );
		$this->assertEquals( 'john@example.com', $retrieved['email'] );
		$this->assertEquals( 'John Doe', $retrieved['full_name'] );
		$this->assertTrue( $retrieved['is_active'] );
		$this->assertEquals( 5, $retrieved['login_count'] );

		// Update.
		$update_data = [
			'username' => 'john_doe', // Primary key
			'login_count' => 6,
			'last_login' => '2024-01-16 11:00:00',
		];

		$result = $table::update_single( $update_data );
		$this->assertTrue( $result );

		$updated = $table::get_by_id( 'john_doe' );
		$this->assertEquals( 6, $updated['login_count'] );
		$this->assertEquals( '2024-01-16 11:00:00', $updated['last_login']->format( 'Y-m-d H:i:s' ) );

		// Delete.
		$result = $table::delete_many( [ 'john_doe' ], 'username' );
		$this->assertNotFalse( $result );

		$deleted = $table::get_by_id( 'john_doe' );
		$this->assertNull( $deleted );
	}

	/**
	 * Test CRUD operations with binary primary key.
	 *
	 * @test
	 */
	public function should_perform_crud_with_binary_primary_key() {
		$table = $this->get_binary_primary_key_table();
		Register::table( $table );

		// Generate UUID and convert to binary.
		$uuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
		$binary_uuid = hex2bin( str_replace( '-', '', $uuid ) );
		$hash_key = hash( 'sha256', 'test_data', true );

		// Insert.
		$data = [
			'binary_id' => $binary_uuid,
			'name' => 'Test Resource',
			'hash_key' => $hash_key,
			'type' => 'document',
			'data' => 'Some binary data content',
			'version' => 1,
			'created_at' => '2024-01-15 12:00:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		// Read by binary primary key.
		$retrieved = $table::get_by_id( $binary_uuid );
		$this->assertNotNull( $retrieved );
		$this->assertEquals( $binary_uuid, $retrieved['binary_id'] );
		$this->assertEquals( 'Test Resource', $retrieved['name'] );
		$this->assertEquals( $hash_key, $retrieved['hash_key'] );
		$this->assertEquals( 'document', $retrieved['type'] );

		// Update using binary primary key.
		$update_data = [
			'binary_id' => $binary_uuid, // Primary key
			'version' => 2,
			'name' => 'Updated Resource',
		];

		$result = $table::update_single( $update_data );
		$this->assertTrue( $result );

		$updated = $table::get_by_id( $binary_uuid );
		$this->assertEquals( 2, $updated['version'] );
		$this->assertEquals( 'Updated Resource', $updated['name'] );

		// Query by binary column.
		$results = $table::get_all_by( 'hash_key', $hash_key );
		$this->assertCount( 1, $results );
		$this->assertEquals( $binary_uuid, $results[0]['binary_id'] );

		// Delete using binary primary key.
		$result = $table::delete_many( [ $binary_uuid ], 'binary_id' );
		$this->assertNotFalse( $result );

		$deleted = $table::get_by_id( $binary_uuid );
		$this->assertNull( $deleted );
	}

	/**
	 * Test composite primary key operations.
	 *
	 * @test
	 */
	public function should_handle_composite_string_binary_primary_key() {
		$table = $this->get_composite_string_binary_key_table();
		Register::table( $table );

		$this->assertTrue( $table->exists() );

		// Prepare composite key values.
		$tenant_id = '550e8400-e29b-41d4-a716-446655440000'; // UUID
		$resource_hash = sha1( 'resource_content', true ); // Binary SHA1

		// Insert.
		$data = [
			'tenant_id' => $tenant_id,
			'resource_hash' => $resource_hash,
			'resource_type' => 'image',
			'resource_name' => 'profile_picture.jpg',
			'is_public' => true,
			'access_count' => 10,
			'last_accessed' => '2024-01-15 14:00:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		// Since this table has composite primary key, we need to query differently.
		// We'll query by one of the key columns.
		$results = $table::get_all_by( 'tenant_id', $tenant_id );
		$this->assertCount( 1, $results );
		$this->assertEquals( $tenant_id, $results[0]['tenant_id'] );
		$this->assertEquals( $resource_hash, $results[0]['resource_hash'] );
		$this->assertEquals( 'image', $results[0]['resource_type'] );

		// Insert another resource for same tenant.
		$resource_hash2 = sha1( 'another_resource', true );
		$data2 = [
			'tenant_id' => $tenant_id,
			'resource_hash' => $resource_hash2,
			'resource_type' => 'document',
			'resource_name' => 'report.pdf',
			'is_public' => false,
			'access_count' => 0,
		];

		$result = $table::insert( $data2 );
		$this->assertNotFalse( $result );

		// Query all resources for tenant.
		$results = $table::get_all_by( 'tenant_id', $tenant_id );
		$this->assertCount( 2, $results );

		// Test uniqueness of composite key - inserting duplicate should fail.
		$duplicate_data = [
			'tenant_id' => $tenant_id,
			'resource_hash' => $resource_hash, // Same composite key
			'resource_type' => 'video',
			'resource_name' => 'duplicate.mp4',
		];

		$this->expectException( DatabaseQueryException::class );
		$table::insert( $duplicate_data );
	}

	/**
	 * Test UUID string as primary key.
	 *
	 * @test
	 */
	public function should_handle_uuid_string_primary_key() {
		$table = $this->get_uuid_primary_key_table();
		Register::table( $table );

		// Generate UUIDs.
		$uuid1 = '123e4567-e89b-12d3-a456-426614174000';
		$uuid2 = '987fcdeb-51a2-43f1-b321-123456789abc';

		// Insert first entity.
		$data1 = [
			'uuid' => $uuid1,
			'entity_type' => 'user',
			'entity_data' => json_encode( [ 'name' => 'Alice', 'role' => 'admin' ] ),
			'is_processed' => false,
			'created_at' => '2024-01-15 09:00:00',
		];

		$result = $table::insert( $data1 );
		$this->assertNotFalse( $result );

		// Insert second entity.
		$data2 = [
			'uuid' => $uuid2,
			'entity_type' => 'order',
			'entity_data' => json_encode( [ 'total' => 99.99, 'items' => 3 ] ),
			'is_processed' => true,
			'created_at' => '2024-01-15 10:00:00',
		];

		$result = $table::insert( $data2 );
		$this->assertNotFalse( $result );

		// Read by UUID primary key.
		$entity1 = $table::get_by_id( $uuid1 );
		$this->assertNotNull( $entity1 );
		$this->assertEquals( $uuid1, $entity1['uuid'] );
		$this->assertEquals( 'user', $entity1['entity_type'] );
		$this->assertIsArray( $entity1['entity_data'] );
		$this->assertEquals( 'Alice', $entity1['entity_data']['name'] );

		$entity2 = $table::get_by_id( $uuid2 );
		$this->assertNotNull( $entity2 );
		$this->assertEquals( $uuid2, $entity2['uuid'] );
		$this->assertEquals( 'order', $entity2['entity_type'] );
		$this->assertEquals( 99.99, $entity2['entity_data']['total'] );

		// Update by UUID.
		$update_data = [
			'uuid' => $uuid1,
			'is_processed' => true,
		];

		$result = $table::update_single( $update_data );
		$this->assertTrue( $result );

		$updated = $table::get_by_id( $uuid1 );
		$this->assertTrue( $updated['is_processed'] );

		// Delete by UUID.
		$result = $table::delete_many( [ $uuid1 ], 'uuid' );
		$this->assertNotFalse( $result );

		$deleted = $table::get_by_id( $uuid1 );
		$this->assertNull( $deleted );

		// Verify second entity still exists.
		$entity2_check = $table::get_by_id( $uuid2 );
		$this->assertNotNull( $entity2_check );
	}

	/**
	 * Test email as primary key.
	 *
	 * @test
	 */
	public function should_handle_email_primary_key() {
		$table = $this->get_email_primary_key_table();
		Register::table( $table );

		// Test emails.
		$emails = [
			'alice@example.com',
			'bob@test.org',
			'charlie+tag@domain.co.uk',
		];

		// Insert subscribers.
		foreach ( $emails as $index => $email ) {
			$data = [
				'email' => $email,
				'name' => 'User ' . ( $index + 1 ),
				'is_verified' => $index === 0, // Only first is verified
				'is_subscribed' => true,
				'subscription_type' => $index === 0 ? 'premium' : 'basic',
				'subscribed_at' => '2024-01-' . sprintf( '%02d', 10 + $index ) . ' 12:00:00',
			];

			$result = $table::insert( $data );
			$this->assertNotFalse( $result );
		}

		// Read by email primary key.
		$subscriber = $table::get_by_id( 'alice@example.com' );
		$this->assertNotNull( $subscriber );
		$this->assertEquals( 'alice@example.com', $subscriber['email'] );
		$this->assertEquals( 'User 1', $subscriber['name'] );
		$this->assertTrue( $subscriber['is_verified'] );
		$this->assertEquals( 'premium', $subscriber['subscription_type'] );

		$lowercase = $table::get_by_id( 'alice@example.com' );
		$this->assertNotNull( $lowercase );

		// Update subscription status.
		$update_data = [
			'email' => 'bob@test.org',
			'is_subscribed' => false,
			'subscription_type' => null,
		];

		$result = $table::update_single( $update_data );
		$this->assertTrue( $result );

		$updated = $table::get_by_id( 'bob@test.org' );
		$this->assertFalse( $updated['is_subscribed'] );
		$this->assertNull( $updated['subscription_type'] );

		// Query by verification status.
		$verified = $table::get_all_by( 'is_verified', true );
		$this->assertCount( 1, $verified );
		$this->assertEquals( 'alice@example.com', $verified[0]['email'] );

		// Delete by email.
		$result = $table::delete_many( [ 'charlie+tag@domain.co.uk' ] );
		$this->assertNotFalse( $result );

		$deleted = $table::get_by_id( 'charlie+tag@domain.co.uk' );
		$this->assertNull( $deleted );

		// Test case sensitivity - MySQL is case-insensitive by default.
		$uppercase_email = 'ALICE@EXAMPLE.COM';
		$data_uppercase = [
			'email' => $uppercase_email,
			'name' => 'Alice Uppercase',
			'is_verified' => false,
			'is_subscribed' => true,
			'subscribed_at' => '2024-01-15 15:00:00',
		];

		$this->expectException( DatabaseQueryException::class );
		$table::insert( $data_uppercase );
	}

	/**
	 * Test querying and pagination with string primary keys.
	 *
	 * @test
	 */
	public function should_paginate_with_string_primary_key() {
		$table = $this->get_string_primary_key_table();
		Register::table( $table );

		// Insert test users.
		$users = [
			[ 'username' => 'alice', 'email' => 'alice@test.com', 'full_name' => 'Alice Smith', 'is_active' => true, 'login_count' => 10 ],
			[ 'username' => 'bob', 'email' => 'bob@test.com', 'full_name' => 'Bob Jones', 'is_active' => true, 'login_count' => 5 ],
			[ 'username' => 'charlie', 'email' => 'charlie@test.com', 'full_name' => 'Charlie Brown', 'is_active' => false, 'login_count' => 0 ],
			[ 'username' => 'david', 'email' => 'david@test.com', 'full_name' => 'David Wilson', 'is_active' => true, 'login_count' => 15 ],
			[ 'username' => 'eve', 'email' => 'eve@test.com', 'full_name' => 'Eve Davis', 'is_active' => true, 'login_count' => 20 ],
		];

		foreach ( $users as $user ) {
			$table::insert( $user );
		}

		// Paginate with ordering by string primary key.
		$page1 = $table::paginate( [], 2, 1, [ '*' ], '', '', [], ARRAY_A );
		$this->assertCount( 2, $page1 );
		$this->assertEquals( 'alice', $page1[0]['username'] ); // Alphabetically first

		$page2 = $table::paginate( [], 2, 2, [ '*' ], '', '', [], ARRAY_A );
		$this->assertCount( 2, $page2 );
		$this->assertEquals( 'charlie', $page2[0]['username'] );

		// Filter active users.
		$args = [
			[
				'column' => 'is_active',
				'value' => 1,
				'operator' => '=',
			],
		];

		$active_users = $table::paginate( $args, 10, 1, [ '*' ], '', '', [], ARRAY_A );
		$this->assertCount( 4, $active_users ); // alice, bob, david, eve

		// Order by login_count.
		$args = [
			'orderby' => 'login_count',
			'order' => 'DESC',
		];

		$ordered = $table::paginate( $args, 10, 1, [ '*' ], '', '', [], ARRAY_A );
		$this->assertEquals( 'eve', $ordered[0]['username'] ); // Highest login count
		$this->assertEquals( 'david', $ordered[1]['username'] );
	}

	/**
	 * Test bulk operations with string and binary primary keys.
	 *
	 * @test
	 */
	public function should_handle_bulk_operations_with_non_integer_keys() {
		$table = $this->get_string_primary_key_table();
		Register::table( $table );

		// Bulk insert.
		$users = [
			[ 'username' => 'user1', 'email' => 'user1@test.com', 'full_name' => 'User One', 'is_active' => true ],
			[ 'username' => 'user2', 'email' => 'user2@test.com', 'full_name' => 'User Two', 'is_active' => true ],
			[ 'username' => 'user3', 'email' => 'user3@test.com', 'full_name' => 'User Three', 'is_active' => false ],
		];

		$result = $table::insert_many( $users );
		$this->assertNotFalse( $result );

		// Verify all inserted.
		$all_users = $table::get_all();
		$count = 0;
		foreach ( $all_users as $user ) {
			$count++;
		}
		$this->assertEquals( 3, $count );

		// Bulk update.
		$updates = [
			[ 'username' => 'user1', 'is_active' => false, 'login_count' => 1 ],
			[ 'username' => 'user2', 'is_active' => false, 'login_count' => 2 ],
		];

		$result = $table::update_many( $updates );
		$this->assertTrue( $result );

		// Verify updates.
		$user1 = $table::get_by_id( 'user1' );
		$this->assertFalse( $user1['is_active'] );
		$this->assertEquals( 1, $user1['login_count'] );

		$user2 = $table::get_by_id( 'user2' );
		$this->assertFalse( $user2['is_active'] );
		$this->assertEquals( 2, $user2['login_count'] );

		// Bulk delete.
		$result = $table::delete_many( [ 'user1', 'user3' ], 'username' );
		$this->assertEquals( 2, $result ); // 2 rows deleted

		// Verify only user2 remains.
		$remaining = $table::get_all();
		$count = 0;
		$last_user = null;
		foreach ( $remaining as $user ) {
			$count++;
			$last_user = $user;
		}
		$this->assertEquals( 1, $count );
		$this->assertEquals( 'user2', $last_user['username'] );
	}

	/**
	 * Test upsert operations with string primary key.
	 *
	 * @test
	 */
	public function should_handle_upsert_with_string_primary_key() {
		$table = $this->get_string_primary_key_table();
		Register::table( $table );

		$data = [
			'username' => 'test_user',
			'email' => 'test@example.com',
			'full_name' => 'Test User',
			'is_active' => true,
			'login_count' => 0,
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$user = $table::get_by_id( 'test_user' );
		$this->assertNotNull( $user );
		$this->assertEquals( 'test@example.com', $user['email'] );
		$this->assertEquals( 0, $user['login_count'] );

		// Second upsert with same key (should update).
		$data['login_count'] = 5;
		$data['email'] = 'newemail@example.com';

		$result = $table::upsert( $data );
		$this->assertTrue( $result );

		$user = $table::get_by_id( 'test_user' );
		$this->assertNotNull( $user );
		$this->assertEquals( 'newemail@example.com', $user['email'] );
		$this->assertEquals( 5, $user['login_count'] );

		// Verify still only one row.
		$all = $table::get_all();
		$count = 0;
		foreach ( $all as $row ) {
			$count++;
		}
		$this->assertEquals( 1, $count );
	}

	/**
	 * Test edge cases with binary primary keys.
	 *
	 * @test
	 */
	public function should_handle_binary_key_edge_cases() {
		$table = $this->get_binary_primary_key_table();
		Register::table( $table );

		// Test with null bytes in binary key.
		$binary_with_nulls = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F";
		$hash_key = hash( 'sha256', 'test', true );

		$data = [
			'binary_id' => $binary_with_nulls,
			'name' => 'Binary with null bytes',
			'hash_key' => $hash_key,
			'type' => 'special',
			'version' => 1,
			'created_at' => '2024-01-15 10:00:00',
		];

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		// Retrieve and verify.
		$retrieved = $table::get_by_id( $binary_with_nulls );
		$this->assertNotNull( $retrieved );
		$this->assertEquals( $binary_with_nulls, $retrieved['binary_id'] );
		$this->assertEquals( 16, strlen( $retrieved['binary_id'] ) );

		// Test with all zeros binary key.
		$all_zeros = str_repeat( "\x00", 16 );
		$data['binary_id'] = $all_zeros;
		$data['name'] = 'All zeros binary';
		$data['hash_key'] = hash( 'sha256', 'zeros', true );

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$retrieved = $table::get_by_id( $all_zeros );
		$this->assertNotNull( $retrieved );
		$this->assertEquals( $all_zeros, $retrieved['binary_id'] );

		// Test with all ones (0xFF) binary key.
		$all_ones = str_repeat( "\xFF", 16 );
		$data['binary_id'] = $all_ones;
		$data['name'] = 'All ones binary';
		$data['hash_key'] = hash( 'sha256', 'ones', true );

		$result = $table::insert( $data );
		$this->assertNotFalse( $result );

		$retrieved = $table::get_by_id( $all_ones );
		$this->assertNotNull( $retrieved );
		$this->assertEquals( $all_ones, $retrieved['binary_id'] );

		// Verify all three exist.
		$all = $table::get_all();
		$count = 0;
		foreach ( $all as $row ) {
			$count++;
		}
		$this->assertEquals( 3, $count );
	}
}
