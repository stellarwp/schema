<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Tables\Contracts\Table_Interface as Table_Schema_Interface;
use StellarWP\Schema\Register;
use StellarWP\Schema\Schema;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Tables\Table_Schema;

/**
 * Class BuilderTest
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Tests
 */
class BuilderTest extends SchemaTestCase {
	use Table_Fixtures;

	/**
	 * @return array List of tables in this database.
	 */
	public function get_tables() {
		global $wpdb;

		return $wpdb->get_col( 'SHOW TABLES' );
	}

	/**
	 * @param string $table Table name.
	 *
	 * @return array<string> List of fields for this table.
	 */
	public function get_table_columns( $table ) {
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

		$rows = $this->get_table_columns( $table::table_name( true ) );
		$this->assertNotContains( 'something', $rows );

		Register::table( $modded_table );

		$rows = $this->get_table_columns( $table::table_name( true ) );
		$this->assertContains( 'something', $rows );

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

		Register::table( $table_schema );

		// Is version there?
		$table_version      = get_option( $table_schema->get_schema_version_option() );
		$this->assertEquals( $table_schema->get_version(), $table_version );

		// Is version gone?
		$builder->down();
		$table_version      = get_option( $table_schema->get_schema_version_option() );
		$this->assertNotEquals( $table_schema->get_version(), $table_version );

		Register::table( $table_schema );

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

			public function get_definition(): string {
				return '';
			}

			public static function get_columns(): Column_Collection {
				return new Column_Collection();
			}

			public static function get_searchable_columns(): Column_Collection {
				return new Column_Collection();
			}

			public static function get_schema_history(): array {
				return [];
			}

			public static function get_current_schema(): Table_Schema {
				return new Table_Schema( 'fodz', new Column_Collection() );
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

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};
		$klutz_table = new class implements Table_Schema_Interface {
			public static function base_table_name() {
				return 'klutz';
			}

			public function get_definition(): string {
				return '';
			}

			public static function get_columns(): Column_Collection {
				return new Column_Collection();
			}

			public static function get_searchable_columns(): Column_Collection {
				return new Column_Collection();
			}

			public static function get_schema_history(): array {
				return [];
			}

			public static function get_current_schema(): Table_Schema {
				return new Table_Schema( 'klutz', new Column_Collection() );
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

			public static function transform_from_array( array $result_array ) {
				return $result_array;
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

			public function get_definition(): string {
				return '';
			}

			public static function get_columns(): Column_Collection {
				return new Column_Collection();
			}

			public static function get_searchable_columns(): Column_Collection {
				return new Column_Collection();
			}

			public static function get_schema_history(): array {
				return [];
			}

			public static function get_current_schema(): Table_Schema {
				return new Table_Schema( 'zorps', new Column_Collection() );
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

			public static function transform_from_array( array $result_array ) {
				return $result_array;
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

	/**
	 * @test
	 */
	public function should_not_create_tables_during_switch_blog_if_blog_not_installed(): void {
		// Register the table.
		$table = $this->get_simple_table();
		Register::table( $table );
		$table->drop();
		// Sanity check.
		$this->assertFalse( $table->exists() );
		// Set up as if switching to a blog before it's installed, during its creation.
		wp_cache_delete( 'is_blog_installed' );
		// Remove all other filters to avoid side-effects.
		remove_all_filters( 'switch_blog' );

		$builder = Schema::builder();

		add_action( 'switch_blog', [ $builder, 'update_blog_tables' ] );

		do_action( 'switch_blog', 66 );

		$this->assertFalse( $table->exists() );
	}

	/**
	 * @test
	 */
	public function should_create_tables_during_switch_blog_if_blog_installed(): void {
		// Register the table.
		$table = $this->get_simple_table();
		Register::table( $table );
		$table->drop();
		// Sanity check.
		$this->assertFalse( $table->exists() );
		// Set up as if switching to a blog after it's installed.
		wp_cache_set( 'is_blog_installed', true );
		// Remove all other filters to avoid side-effects.
		remove_all_filters( 'switch_blog' );

		$builder = Schema::builder();

		add_action( 'switch_blog', [ $builder, 'update_blog_tables' ] );

		do_action( 'switch_blog', 66 );

		$this->assertTrue( $table->exists() );
	}

	/**
	 * @test
	 */
	public function should_create_tables_during_activate_blog(): void {
		// Register the table.
		$table = $this->get_simple_table();
		Register::table( $table );
		$table->drop();
		// Sanity check.
		$this->assertFalse( $table->exists() );
		// Set up as if switching to a blog after it's installed.
		wp_cache_set( 'is_blog_installed', true );
		// Remove all other filters to avoid side-effects.
		remove_all_filters( 'activate_blog' );

		$builder = Schema::builder();

		add_action( 'activate_blog', [ $builder, 'update_blog_tables' ] );

		do_action( 'activate_blog', 66 );

		$this->assertTrue( $table->exists() );
	}
}
