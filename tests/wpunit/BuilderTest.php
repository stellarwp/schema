<?php

namespace StellarWP\Schema\Tests\Builder;

use StellarWP\Schema\Builder;
use StellarWP\Schema\Tables\Table_Schema_Interface;
use StellarWP\Schema\Container;
use StellarWP\Schema\Register;
use StellarWP\Schema\Schema;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

class BuilderTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * @param string $table Table name.
	 *
	 * @return array<string> List of fields for this table.
	 */
	public function get_table_fields( $table ) {
		global $wpdb;
		$q    = 'select `column_name` from information_schema.columns
					where table_schema = database()
					and `table_name`= %s';
		$rows = $wpdb->get_results( $wpdb->prepare( $q, $table ) );

		return array_map( function ( $row ) {
			return $row->column_name;
		}, $rows );
	}

	/**
	 * @return array List of tables in this database.
	 */
	public function get_tables() {
		global $wpdb;

		return $wpdb->get_col( 'SHOW TABLES' );
	}

	/**
	 * Should tables create/destroy properly.
	 *
	 * @test
	 */
	public function should_up_down_table_schema() {
		$builder = Schema::builder();
		$table   = $this->get_simple_table();

		Register::table( $table );

		$tables  = $this->get_tables();

		$this->assertContains( $table::table_name( true ), $tables );
		$this->assertTrue( $builder->all_tables_exist() );

		$builder->down();

		// Validate expected state.
		$tables = $this->get_tables();
		$this->assertNotContains( $table::table_name( true ), $tables );
		$this->assertFalse( $builder->all_tables_exist() );
	}

	/**
	 * Should tables update properly.
	 *
	 * @test
	 */
	public function should_update_table_when_version_changes() {
		$builder      = Schema::builder();
		$table        = $this->get_simple_table();
		$modded_table = $this->get_modified_simple_table();

		Register::table( $table );

		$tables  = $this->get_tables();

		$this->assertContains( $table::table_name( true ), $tables );
		$this->assertTrue( $builder->all_tables_exist() );

		$rows = $this->get_table_fields( $table::table_name( true ) );
		$this->assertNotContains( 'something', $rows );

		Register::table( $modded_table );

		$rows = $this->get_table_fields( $table::table_name( true ) );
		$this->assertContains( 'something', $rows );

		$builder->down();
	}

	/**
	 * Should fields create/destroy properly.
	 *
	 * @test
	 */
	public function should_up_down_field_schema() {
		$builder      = Schema::builder();
		$table_schema = $this->get_simple_table();
		$field_schema = $this->get_simple_table_field();

		Register::table( $table_schema );
		Register::field( $field_schema );

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertContains( $field, $rows );
		}

		add_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

		// Bring down with dropping disabled (default).
		$builder->down();

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertContains( $field, $rows );
		}

		add_filter( 'stellarwp_drop_field_enabled', '__return_true' );

		// Bring down with dropping enabled.
		$builder->down();

		remove_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );
		remove_filter( 'stellarwp_drop_field_enabled', '__return_true' );

		// Validate expected state.
		$rows = $this->get_table_fields( $field_schema->table_schema()::table_name( true ) );

		foreach ( $field_schema->fields() as $field ) {
			$this->assertNotContains( $field, $rows );
		}

		// Clean up.
		$builder->down();
	}

	/**
	 * Tests the `exists` function finds the fields properly.
	 *
	 * @test
	 */
	public function should_field_exists() {
		$builder      = Schema::builder();
		$table_schema = $this->get_simple_table();
		$field_schema = $this->get_simple_table_field();

		Register::table( $table_schema );
		Register::field( $field_schema );

		$this->assertTrue( $field_schema->exists() );

		// Keep our table - validate the field changes.
		add_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

		// Bring down with dropping disabled (default).
		$builder->down();
		$this->assertTrue( $field_schema->exists() );

		add_filter( 'stellarwp_drop_field_enabled', '__return_true' );

		// Bring down with dropping enabled.
		$builder->down();

		remove_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );
		remove_filter( 'stellarwp_drop_field_enabled', '__return_true' );

		$this->assertFalse( $field_schema->exists() );

		// Cleanup.
		$builder->down();
	}

	/**
	 * The state of the stored version should be stored and removed when we up/down the schema.
	 *
	 * @test
	 */
	public function should_sync_version() {
		$builder      = Schema::builder();
		$table_schema = $this->get_simple_table();
		$field_schema = $this->get_simple_table_field();

		Register::table( $table_schema );

		// Is version there?
		$table_version      = get_option( $table_schema->get_schema_version_option() );
		$this->assertEquals( $table_schema->get_version(), $table_version );

		// Is version gone?
		$builder->down();
		$table_version      = get_option( $table_schema->get_schema_version_option() );
		$this->assertNotEquals( $table_schema->get_version(), $table_version );

		Register::table( $table_schema );
		Register::field( $field_schema );

		// Is version there?
		$table_version      = get_option( $table_schema->get_schema_version_option() );
		$this->assertEquals( $table_schema->get_version(), $table_version );

		// Keep our table - validate the field changes.
		add_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

		// Is version reset?
		$builder->down();

		remove_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

		$table_version      = get_option( $table_schema->get_schema_version_option() );
		$this->assertEquals( $table_schema->get_version(), $table_version );

		// Cleanup.
		$builder->down();
	}

	/**
	 * It should support group when checking for all tables existence
	 *
	 * @test
	 */
	public function should_support_group_when_checking_for_all_tables_existence() {
		add_filter( 'query', static function ( $query ) {
			if ( $query !== 'SHOW TABLES' ) {
				return $query;
			}

			return 'SELECT "fodz" UNION ALL SELECT "klutz" UNION ALL SELECT "zorps"';
		} );

		$fodz_table  = new class implements Table_Schema_Interface {
			public static function base_table_name() {
				return 'fodz';
			}

			public function drop() {}

			public function empty_table() {}

			public function exists() {
				return false;
			}

			public static function get_schema_slug() {
				return 'fodz';
			}

			public function get_sql() {}

			public function get_version(): string {
				return '1.0.0';
			}

			public static function group_name() {
				return 'one';
			}

			public function is_schema_current() {}

			public static function table_name( $with_prefix = true ) {
				return 'fodz';
			}

			public static function uid_column() {}

			public function update() {
				return [];
			}
		};
		$klutz_table = new class implements Table_Schema_Interface {
			public static function base_table_name() {
				return 'klutz';
			}

			public function drop() {}

			public function empty_table() {}

			public function exists() {
				return true;
			}

			public static function get_schema_slug() {
				return 'kluts';
			}

			public function get_sql() {}

			public function get_version(): string {
				return '1.0.0';
			}

			public static function group_name() {
				return 'one';
			}

			public function is_schema_current() {}

			public static function table_name( $with_prefix = true ) {
				return 'klutz';
			}

			public static function uid_column() {}

			public function update() {
				return [];
			}
		};
		$zorps_table = new class implements Table_Schema_Interface {
			public static function base_table_name() {
				return 'zorps';
			}

			public function drop() {}

			public function empty_table() {}

			public function exists() {
				return true;
			}

			public static function get_schema_slug() {
				return 'zorps';
			}

			public function get_sql() {}

			public function get_version(): string {
				return '1.0.0';
			}

			public static function group_name() {
				return 'two';
			}

			public function is_schema_current() {}

			public static function table_name( $with_prefix = true ) {
				return 'zorps';
			}

			public static function uid_column() {}

			public function update() {
				return [];
			}
		};

		$builder = Schema::builder();

		Register::table( $fodz_table );
		Register::table( $klutz_table );
		Register::table( $zorps_table );

		$this->assertTrue( $builder->all_tables_exist() );
		$this->assertTrue( $builder->all_tables_exist( 'one' ) );
		$this->assertTrue( $builder->all_tables_exist( 'two' ) );
		$this->assertTrue( $builder->all_tables_exist( 'three' ) );

		Register::remove_table( $fodz_table );
		Register::remove_table( $klutz_table );
		Register::remove_table( $zorps_table );
	}
}
