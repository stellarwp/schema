<?php

namespace StellarWP\Schema\Tests\Traits;

use StellarWP\Schema\Activation;
use StellarWP\Schema\Builder;
use StellarWP\Schema\Fields\Contracts\Field;
use StellarWP\Schema\Tables\Contracts\Table;

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
			protected static $uid_column = 'id';

			protected function get_definition() {
				global $wpdb;
				$table_name      = self::table_name( true );
				$charset_collate = $wpdb->get_charset_collate();

				return "
					CREATE TABLE `{$table_name}` (
						`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` varchar(25) NOT NULL,
						`slug` varchar(25) NOT NULL,
						`something` varchar(25) NOT NULL,
						PRIMARY KEY (`id`),
						KEY `slug` (`slug`),
						KEY `something` (`something`)
					) {$charset_collate};
				";
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
			protected static $uid_column = 'id';

			protected function get_definition() {
				global $wpdb;
				$table_name      = self::table_name( true );
				$charset_collate = $wpdb->get_charset_collate();

				return "
					CREATE TABLE `{$table_name}` (
						`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` varchar(25) NOT NULL,
						`slug` varchar(25) NOT NULL,
						PRIMARY KEY (`id`),
						KEY `slug` (`slug`)
					) {$charset_collate};
				";
			}
		};

		return $table;
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
			protected static $uid_column = 'id';

			protected function get_definition() {
				global $wpdb;
				$table_name      = self::table_name( true );
				$charset_collate = $wpdb->get_charset_collate();

				return "
					CREATE TABLE `{$table_name}` (
						`id` int(11) UNSIGNED NOT NULL,
						`name` varchar(25) NOT NULL,
						`slug` varchar(25) NOT NULL
					) {$charset_collate};
				";
			}
		};

		return $table;
	}

	/**
	 * Get a simple table field class.
	 */
	public function get_simple_table_field() {
		$field  = new class extends Field {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'simple';
			protected static $schema_slug = 'simple-bork';

			protected $fields = [
				'bork',
			];

			protected function get_definition() {
				return "
					`bork` int(11) UNSIGNED NOT NULL,
					KEY `bork` (`bork`)
				";
			}
		};

		return $field;
	}

	public function get_foreign_key_table() {
		$table = new class extends Table {
			const SCHEMA_VERSION = '1.0.0';

			protected static $base_table_name = 'foreignkey';
			protected static $group = 'bork';
			protected static $schema_slug = 'bork-with-foreignkey';
			protected static $uid_column = 'id';

			protected function get_definition() {
				global $wpdb;
				$table_name      = self::table_name( true );
				$charset_collate = $wpdb->get_charset_collate();

				return "
					CREATE TABLE `{$table_name}` (
						`id` int(11) UNSIGNED NOT NULL,
						`name` varchar(25) NOT NULL,
						`simple_id` int(11) UNSIGNED NOT NULL
					) {$charset_collate};
				";
			}

			protected function after_update( array $results ) {
				if ( $this->has_foreign_key( 'simple_id' ) ) {
					return $results;
				}

				global $wpdb;
				$table_name   = $this->table_name();
				$simple_table = $wpdb->prefix . 'simple';
				$updated      = $wpdb->query( "ALTER TABLE $table_name ADD FOREIGN KEY (simple_id) REFERENCES $simple_table(id)" );

				$result = $updated ?
					'FOREIGN KEY added to ' . $table_name . ' on column simple_id to table ' . $simple_table . ' on column id' :
					'Failed to add FOREIGN KEY NOT to ' . $table_name . ' on column simple_id to table ' . $simple_table . ' on column id';

				$results[] = $result;

				return $results;
			}
		};

		return $table;
	}
}
