<?php

namespace StellarWP\Schema\Tests\Builder;

use StellarWP\Schema\Builder\Abstract_Custom_Field;
use StellarWP\Schema\Builder\Field_Schema_Interface;
use StellarWP\Schema\Builder\Schema_Builder;
use StellarWP\Schema\Builder\Table_Schema_Interface;
use StellarWP\Schema\Container;
use StellarWP\Schema\Register;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\SimpleCustomTable;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

class Schema_BuilderTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * Should tables create/destroy properly.
	 *
	 * @test
	 */
	public function should_up_down_table_schema() {
		$builder = Container::init()->make( Schema_Builder::class );
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
	 * Should fields create/destroy properly.
	 *
	 * @test
	 */
	public function should_up_down_field_schema() {
		$builder      = Container::init()->make( Schema_Builder::class );
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

		// Activate.
		$builder->down();

		remove_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

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
		$builder      = Container::init()->make( Schema_Builder::class );
		$table_schema = $this->get_simple_table();
		$field_schema = $this->get_simple_table_field();

		Register::table( $table_schema );
		Register::field( $field_schema );

		// Keep our table - validate the field changes.
		add_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

		$builder->up( true );

		remove_filter( 'stellarwp_schema_table_drop_simple', '__return_false' );

		$this->assertTrue( $field_schema->exists() );

		$builder->down();

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
		$field_schema = $this->custom_field_schema();
		$this->given_a_field_schema_exists( $field_schema );
		$schema_builder = Container::init()->make( Schema_Builder::class );
		$schema_builder->up();

		// Is version there?
		$field_version      = get_option( $field_schema::SCHEMA_VERSION_OPTION );
		$this->assertEquals( $field_schema::SCHEMA_VERSION, $field_version );

		// Is version gone?
		$schema_builder->down();
		$field_version      = get_option( $field_schema::SCHEMA_VERSION_OPTION );
		$this->assertNotEquals( $field_schema::SCHEMA_VERSION, $field_version );
	}

	/**
	 * Add this schema to the registered list.
	 *
	 * @param Field_Schema_Interface $field_schema
	 */
	public function given_a_field_schema_exists( $field_schema ) {
		add_filter( 'tec_events_custom_tables_v1_field_schemas', function ( $fields ) use ( $field_schema ) {
			return array_merge( $fields, [ $field_schema ] );
		} );
	}

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
	 * @return Abstract_Custom_Field
	 */
	public function custom_field_schema() {
		return new class extends Abstract_Custom_Field {
			const SCHEMA_VERSION = '1.0.0';
			const SCHEMA_VERSION_OPTION = 'custom_field_version_key';

			public function fields() {
				return [ 'bob', 'frank' ];
			}

			public function table_schema() {
				return $this->get_simple_table();
			}

			public function get_update_sql() {
				global $wpdb;
				$table_name      = $this->table_schema()::table_name( true );
				$charset_collate = $wpdb->get_charset_collate();

				return "CREATE TABLE `{$table_name}` (
			`bob` LONGTEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			`frank` TINYINT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			) {$charset_collate};";
			}
		};
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
			public static function uid_column() {
			}

			public function empty_table() {
			}

			public function drop() {
			}

			public function update() {
			}

			public static function table_name( $with_prefix = true ) {
				return 'fodz';
			}

			public static function base_table_name() {
			}

			public function is_schema_current() {
			}

			public static function group_name() {
				return 'one';
			}

			public function exists() {
				return false;
			}
		};
		$klutz_table = new class implements Table_Schema_Interface {
			public static function uid_column() {
			}

			public function empty_table() {
			}

			public function drop() {
			}

			public function update() {
			}

			public static function table_name( $with_prefix = true ) {
				return 'klutz';
			}

			public static function base_table_name() {
			}

			public function is_schema_current() {
			}

			public static function group_name() {
				return 'one';
			}

			public function exists() {
				return true;
			}
		};
		$zorps_table = new class implements Table_Schema_Interface {
			public static function uid_column() {
			}

			public function empty_table() {
			}

			public function drop() {
			}

			public function update() {
			}

			public static function table_name( $with_prefix = true ) {
				return 'zorps';
			}

			public static function base_table_name() {
			}

			public function is_schema_current() {
			}

			public static function group_name() {
				return 'two';
			}

			public function exists() {
				return true;
			}
		};
		$tables      = [ $fodz_table, $klutz_table, $zorps_table ];
		add_filter( 'tec_events_custom_tables_v1_table_schemas', static function () use ( $tables ) {
			return $tables;
		} );

		$schema_builder = new Schema_Builder;

		$this->assertTrue( $schema_builder->all_tables_exist() );
		$this->assertTrue( $schema_builder->all_tables_exist( 'one' ) );
		$this->assertTrue( $schema_builder->all_tables_exist( 'two' ) );
		$this->assertFalse( $schema_builder->all_tables_exist( 'three' ) );
	}
}
