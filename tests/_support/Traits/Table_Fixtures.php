<?php

namespace StellarWP\Schema\Tests\Traits;

use StellarWP\Schema\Activation;
use StellarWP\Schema\Builder;
use StellarWP\Schema\Columns\ID;
use StellarWP\Schema\Config;
use StellarWP\Schema\Tables\Contracts\Table;
use StellarWP\Schema\Tables\Table_Schema;
use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Columns\String_Column;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Columns\Integer_Column;

trait Table_Fixtures {
	private function assert_custom_tables_exist() {
		$schema_builder = Config::get_container()->get( Builder::class );
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertTrue( $table_schema->exists() );
		}
	}

	private function assert_custom_tables_not_exist(){
		$schema_builder = Config::get_container()->get( Builder::class );
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertFalse( $table_schema->exists() );
		}
	}

	private function given_the_custom_tables_do_not_exist() {
		$schema_builder = Config::get_container()->get( Builder::class );
		$schema_builder->down();
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertFalse( $table_schema->exists() );
		}
	}

	private function given_the_custom_tables_do_exist() {
		$schema_builder = Config::get_container()->get( Builder::class );
		$schema_builder->up();
		$this->assert_custom_tables_exist();
	}

	private function given_the_initialization_transient_expired() {
		delete_transient( Activation::ACTIVATION_TRANSIENT );
	}

	private function given_custom_tables_are_not_initialized() {
		delete_transient( Activation::ACTIVATION_TRANSIENT );
	}

	/**
	 * Get a fake table to verify its creation.
	 */
	public function get_modified_simple_table() {
		$table  = new class extends Table {
			const SCHEMA_VERSION = '2.0.0';
			protected static $base_table_name = 'simple';
			protected static $group = 'bork';
			protected static $schema_slug = 'bork-simple';

			public static function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new ID( 'id' ) )->set_length( 11 )->set_type( Column::COLUMN_TYPE_INT );
				$columns[] = ( new String_Column( 'name' ) )->set_length( 25 );
				$columns[] = ( new String_Column( 'slug' ) )->set_length( 25 )->set_is_index( true );
				$columns[] = ( new String_Column( 'something' ) )->set_length( 25 )->set_is_index( true );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};

		return $table;
	}

	/**
	 * Get a fake table to verify its creation.
	 */
	public function get_simple_table() {
		$table  = new class extends Table {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'simple';
			protected static $group = 'bork';
			protected static $schema_slug = 'bork-simple';

			public static function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new ID( 'id' ) )->set_length( 11 )->set_type( Column::COLUMN_TYPE_INT );
				$columns[] = ( new String_Column( 'name' ) )->set_length( 25 );
				$columns[] = ( new String_Column( 'slug' ) )->set_length( 25 )->set_is_index( true );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};

		return $table;
	}

	/**
	 * Get a fake table to verify its creation.
	 */
	public function get_simple_table_alt_group(): Table {
		return new class extends Table {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'simple-alt';
			protected static $group = 'test';
			protected static $schema_slug = 'bork-simple-alt';

			public static function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new ID( 'id' ) )->set_length( 11 )->set_type( Column::COLUMN_TYPE_INT );
				$columns[] = ( new String_Column( 'name' ) )->set_length( 25 );
				$columns[] = ( new String_Column( 'slug' ) )->set_length( 25 )->set_is_index( true );

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
	 * Get a fake table to verify its creation.
	 */
	public function get_indexless_table() {
		$table  = new class extends Table {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'noindex';
			protected static $group = 'bork';
			protected static $schema_slug = 'bork-noindex';

			public static function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new Integer_Column( 'id' ) )->set_length( 11 )->set_signed( false )->set_type( Column::COLUMN_TYPE_INT );
				$columns[] = ( new String_Column( 'name' ) )->set_length( 25 );
				$columns[] = ( new String_Column( 'slug' ) )->set_length( 25 );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};

		return $table;
	}

	public function get_foreign_key_table() {
		$table = new class extends Table {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'foreignkey';
			protected static $group = 'bork';
			protected static $schema_slug = 'bork-with-foreignkey';

			public static function get_schema_history(): array {
				$columns = new Column_Collection();

				$columns[] = ( new Integer_Column( 'id' ) )->set_length( 11 )->set_signed( false )->set_type( Column::COLUMN_TYPE_INT );
				$columns[] = ( new String_Column( 'name' ) )->set_length( 25 );
				$columns[] = ( new Integer_Column( 'simple_id' ) )->set_length( 11 )->set_signed( false )->set_type( Column::COLUMN_TYPE_INT );

				return [
					static::SCHEMA_VERSION => new Table_Schema( static::table_name( true ), $columns ),
				];
			}

			protected function after_update( array $results ) {
				if ( $this->has_foreign_key( 'simple_id' ) ) {
					return $results;
				}

				global $wpdb;
				$table_name   = static::table_name();
				$simple_table = $wpdb->prefix . 'simple';
				$updated      = $wpdb->query( "ALTER TABLE $table_name ADD FOREIGN KEY (simple_id) REFERENCES $simple_table(id)" );

				$result = $updated ?
					'FOREIGN KEY added to ' . $table_name . ' on column simple_id to table ' . $simple_table . ' on column id' :
					'Failed to add FOREIGN KEY NOT to ' . $table_name . ' on column simple_id to table ' . $simple_table . ' on column id';

				$results[] = $result;

				return $results;
			}

			public static function transform_from_array( array $result_array ) {
				return $result_array;
			}
		};

		return $table;
	}
}
