<?php

namespace StellarWP\Schema\Tests\Traits;

use StellarWP\Schema\Activation;
use StellarWP\Schema\Builder\Schema_Builder;
use StellarWP\Schema\Container;

trait Table_Fixtures {
	private function assert_custom_tables_exist() {
		$schema_builder = Container::init()->make( Schema_Builder::class );
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertTrue( $table_schema->exists() );
		}
	}

	private function assert_custom_tables_not_exist(){
		$schema_builder = Container::init()->make( Schema_Builder::class );
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertFalse( $table_schema->exists() );
		}
	}

	private function given_the_custom_tables_do_not_exist() {
		$schema_builder = Container::init()->make( Schema_Builder::class );
		$schema_builder->down();
		foreach ( $schema_builder->get_registered_table_schemas() as $table_schema ) {
			$this->assertFalse( $table_schema->exists() );
		}
	}

	private function given_the_custom_tables_do_exist() {
		$schema_builder = Container::init()->make( Schema_Builder::class );
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
	public function get_simple_table() {
		$table  = new class extends Abstract_Custom_Table {
			const SCHEMA_VERSION = '1.0.0';
			const SCHEMA_VERSION_OPTION = 'test_simple';

			protected static $base_table_name = 'simple';
			protected static $group = 'test';
			protected static $uid_column = 'id';

			public function get_update_sql() {
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
}
