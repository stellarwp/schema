<?php
namespace StellarWP\Schema\Tests\Builder;

use StellarWP\Schema\Builder\Abstract_Custom_Table;
use StellarWP\Schema\Builder\Schema_Builder;
use StellarWP\Schema\Container;
use StellarWP\Schema\Register;
use StellarWP\Schema\Schema;
use StellarWP\Schema\Tables;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits;

class Full_ActivationTest extends SchemaTestCase {
	use Traits\Table_Fixtures;

	/**
	 * It should add custom tables names as properties on wpdb object
	 *
	 * @test
	 */
	public function should_add_custom_tables_names_as_properties_on_wpdb_object() {
		Register::table( $this->get_simple_table() );

		$collection = Container::init()->make( Tables\Collection::class );

		$schema        = Container::init()->make( Schema_Builder::class );
		$table_schemas = $schema->get_registered_table_schemas();
		$this->assertNotEmpty( $table_schemas );
		global $wpdb;

		foreach ( $table_schemas as $table_schema ) {
			$class = get_class( $table_schema );
			$table_name = $class::base_table_name();
			$this->assertEquals( $wpdb->prefix . $table_name, $wpdb->{$table_name} );
		}
	}
}
