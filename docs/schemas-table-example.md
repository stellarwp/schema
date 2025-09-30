# Example table schema class

## Version 3.0+ (Recommended)

Here is an example table schema using the new Column and Index system:

```php
<?php
namespace Boom\Shakalaka\Tables;

use Boom\Shakalaka\StellarWP\Schema\Tables\Contracts\Table;
use Boom\Shakalaka\StellarWP\Schema\Tables\Table_Schema;
use Boom\Shakalaka\StellarWP\Schema\Collections\Column_Collection;
use Boom\Shakalaka\StellarWP\Schema\Collections\Index_Collection;
use Boom\Shakalaka\StellarWP\Schema\Columns\ID;
use Boom\Shakalaka\StellarWP\Schema\Columns\String_Column;
use Boom\Shakalaka\StellarWP\Schema\Columns\Text_Column;
use Boom\Shakalaka\StellarWP\Schema\Columns\Integer_Column;
use Boom\Shakalaka\StellarWP\Schema\Columns\Created_At;
use Boom\Shakalaka\StellarWP\Schema\Columns\Updated_At;
use Boom\Shakalaka\StellarWP\Schema\Columns\Column_Types;
use Boom\Shakalaka\StellarWP\Schema\Indexes\Classic_Index;
use Boom\Shakalaka\StellarWP\Schema\Indexes\Unique_Key;

class Sandwiches extends Table {
	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION = '1.0.0';

	/**
	 * {@inheritdoc}
	 */
	protected static $base_table_name = 'sandwiches';

	/**
	 * {@inheritdoc}
	 */
	protected static $group = 'food';

	/**
	 * {@inheritdoc}
	 */
	protected static $schema_slug = 'food-sandwiches';

	/**
	 * {@inheritdoc}
	 */
	public static function get_schema_history(): array {
		$table_name = static::table_name( true );

		return [
			'1.0.0' => function() use ( $table_name ) {
				$columns = new Column_Collection();

				// Auto-incrementing primary key
				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column_Types::BIGINT )
					->set_auto_increment( true );

				// String columns
				$columns[] = ( new String_Column( 'name' ) )
					->set_type( Column_Types::VARCHAR )
					->set_length( 100 )
					->set_searchable( true );

				$columns[] = ( new String_Column( 'type' ) )
					->set_type( Column_Types::VARCHAR )
					->set_length( 50 )
					->set_default( 'classic' );

				// Text column for description
				$columns[] = ( new Text_Column( 'description' ) )
					->set_type( Column_Types::TEXT )
					->set_nullable( true );

				// Integer for price (in cents)
				$columns[] = ( new Integer_Column( 'price_cents' ) )
					->set_type( Column_Types::INT )
					->set_length( 11 )
					->set_default( 0 );

				// Timestamp columns
				$columns[] = new Created_At( 'created_at' );
				$columns[] = new Updated_At( 'updated_at' );

				// Define indexes
				$indexes = new Index_Collection();
				$indexes[] = new Classic_Index( [ 'type' ] );
				$indexes[] = new Unique_Key( [ 'name' ] );

				return new Table_Schema( $table_name, $columns, $indexes );
			},
		];
	}
}
```

## Version 2.x (Legacy)

For backwards compatibility, you can still use the `get_definition()` method as long as you switch the visibility to `public` and still implement the `get_schema_history()` method:

```php
<?php
namespace Boom\Shakalaka\Tables;

use Boom\Shakalaka\StellarWP\Schema\Tables\Contracts\Table;

class Sandwiches extends Table {
	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION = '1.0.0';

	/**
	 * {@inheritdoc}
	 */
	protected static $base_table_name = 'sandwiches';

	/**
	 * {@inheritdoc}
	 */
	protected static $group = 'food';

	/**
	 * {@inheritdoc}
	 */
	protected static $schema_slug = 'food-sandwiches';

	/**
	 * {@inheritdoc}
	 */
	protected static $uid_column = 'id';

	/**
	 * {@inheritdoc}
	 */
	public function get_definition(): string {
		global $wpdb;
		$table_name = self::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "
			CREATE TABLE `{$table_name}` (
				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`name` varchar(50) NOT NULL,
				PRIMARY KEY (`id`)
			) {$charset_collate};
		";
	}
}
```

## Anatomy of a table schema class

### `class Sandwiches extends Tables\Contracts\Table`

It is highly recommended that all table schema classes extend the `PREFIX\StellarWP\Schema\Tables\Contracts\Table` class (obviously, with the Strauss-enabled prefixing in place), but if you really want to change things up, you can opt to implement `PREFIX\StellarWP\Schema\Tables\Contracts\Schema_Interface` instead.

### `const SCHEMA_VERSION`

This constant holds the base version number of the table schema. As you change the fields within `::get_definition()`, you will need to be sure to update this constant so that table updates occur when they should.

### `protected static $base_table_name`

This is the base name of the table without `$wpdb->prefix` applied. With the above example in mind, the value can be accessed using `PREFIX\Tables\Sandwiches::base_table_name()`. It is used for a couple of purposes:

* It is used to generate the name of the table in the `::get_description()` method.
* It is the index that is used in the `PREFIX\StellarWP\Schema\Tables\Collection` iterator - the class that collects and stores registered table schemas.
* It is used to associate field schemas to table schemas.

### `protected static $group`

This value allows you to group table schemas together so you can do interesting things with them programmatically. There's no explicit use for groups out of the box other than providing some tooling for finding table schemas within a group.

Here's an example:

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema;

// This registers a the Sandwiches table that has `$group` set to `food`.
Register::table( Tables\Sandwiches::class );

// Let's pretend there's a Bricks table schema that has `$group` set to `not-food`.
Register::table( Tables\Bricks::class );

// This returns all of the table schemas regardless of their group.
$tables_in_group_boom = Schema::tables();

// This returns all of the table schemas that have `$group` set to `food`.
$tables_in_group_boom = Schema::tables()->get_by_group( 'food' );

foreach ( $tables_in_group_boom as $table_schema ) {
	echo $table_schema->get_sql() . "\n";
}
```

### `protected static $schema_slug`

This is the slug of this table schema and is used to generate the `wp_options` key that is used to store the table schema version. Those options are in the following format: `stellarwp_schema_version_{$schema_slug}`.

### `protected static $uid_column`

This is the name of the column that is used to uniquely identify rows within the table.

### `public function get_definition(): string`

This is the base definition of the table.

