<?php

namespace StellarWP\Schema\Tests;

use StellarWP\Schema\Builder;
use StellarWP\Schema\Config;
use StellarWP\Schema\Register;

class FullActivationTest extends SchemaTestCase {
	use Traits\Table_Fixtures;

	/**
	 * It should add custom tables names as properties on wpdb object
	 *
	 * @test
	 */
	public function should_add_custom_tables_names_as_properties_on_wpdb_object() {
		global $wpdb;

		Register::table( $this->get_simple_table() );

		$builder       = Config::get_container()->get( Builder::class );
		$table_schemas = $builder->get_registered_table_schemas();

		$this->assertNotEmpty( $table_schemas );

		foreach ( $table_schemas as $table_schema ) {
			$table_name = $table_schema::base_table_name();
			$this->assertEquals( $wpdb->prefix . $table_name, $wpdb->{$table_name} );
		}
	}
}
