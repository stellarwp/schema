<?php declare( strict_types=1 );

namespace StellarWP\Schema\Tests\Tables;

use StellarWP\Schema\Config;
use StellarWP\Schema\Register;
use StellarWP\Schema\Tables\Collection;
use StellarWP\Schema\Tests\SchemaTestCase;
use StellarWP\Schema\Tests\Traits\Table_Fixtures;

final class CollectionTest extends SchemaTestCase {

	use Table_Fixtures;

	/**
	 * @var Collection
	 */
	private $collection;

	protected function setUp() {
		parent::setUp();

		$this->get_simple_table()->drop();
		$this->get_indexless_table()->drop();

		Register::tables([
			$this->get_simple_table(),
			$this->get_indexless_table(),
		]);

		$this->collection = Config::get_container()->get( Collection::class );
	}

	public function test_it_has_tables_in_collection() {
		$this->assertSame( 2, $this->collection->count() );
	}

	public function test_it_fetches_table_by_base_table_name() {
		$name  = $this->get_simple_table()::base_table_name();
		$table = $this->collection->get( $name );

		$this->assertSame( $name, $table::base_table_name() );
	}

	public function test_it_gets_tables_by_group() {
		$tables = $this->collection->get_by_group( 'bork' );

		$this->assertSame( 2, $tables->count() );
	}


}
