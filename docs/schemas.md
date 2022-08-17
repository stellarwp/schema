# Schema management

This library is all about managing custom schemas (a.k.a. SQL table definitions). You can do that by declaring both [table schemas](#table-schemas) and [field schemas](#field-schemas) as classes within your project's codebase.

## Where to put your schema class files

Each project is different and this library tries to avoid being overly opinionated. So, put your table and field schema classes wherever it makes sense in your project. If you use namespaces, awesome. That works nicely. If you don't that works too. You'll just need to include your classes before you can register the schemas.

### Example directory stucture

For a plugin that uses composer and namespacing, this is a potential structure for placing your schema classes - putting them in `Fields/` and `Tables/` directories.

```
composer.lock
composer.json
boom-shakalaka.php
src/
	BoomShakalaka/
		Fields/
			A_Field_Schema.php
			Another_Field_Schema.php
		Tables/
			A_Table_Schema.php
			Another_Table_Schema.php
		...other stuff
vendor/
```

## `dbDelta()` under the hood

Under the hood of this library, schema management is done via the [`dbDelta()`](https://developer.wordpress.org/reference/functions/dbdelta/) function within WordPress. That function is battle tested and reliable, so we are standing on the shoulders of champions here. Any schema - whether a table schema of a field schema - has its SQL run through that function to ensure the table definitions are up to date.

It is important to note that `dbDelta()` does not _remove_ fields or indices in a database table. This approach is meant to prevent the accidentaly removal of data. We take a similar stance with this library. There _are_ ways to remove fields and drop tables, but those actions do not happen automatically and are up to you to decide when it is appropriate to do so.

## Table schemas

Table schema classes hold all of the building blocks for getting a custom table to be defined and managed by this library. Table schemas should have their base definition declared in the `::get_definition()` method, however, it is important to note that the final definition SQL (fetched via `::get_sql()`) can be influenced by registered Field Schemas.

### Example table schema class

Here is an example table schema:

```php
<?php
namespace Boom\Shakalaka\Tables;

use Boom\Shakalaka\StellarWP\Schema\Tables;

class Sandwiches extends Tables\Abstract_Table {
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
	protected static $group = 'boom';

	/**
	 * {@inheritdoc}
	 */
	protected static $schema_slug = 'boom-sandwiches';

	/**
	 * {@inheritdoc}
	 */
	protected static $uid_column = 'id';

	/**
	 * {@inheritdoc}
	 */
	protected function get_definition() {
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

### Anatomy of a table schema class

#### `class Whatever extends Tables\Abstract_Table`

It is highly recommended that all table schema classes extend the `PREFIX\StellarWP\Schema\Tables\Abstract_Table` class (obviously, with the Strauss-enabled prefixing in place), but if you really want to change things up, you can opt to implement `PREFIX\StellarWP\Schema\Tables\Table_Schema_Interface` instead.

#### `const SCHEMA_VERSION`

This constant holds the base version number of the table schema. As you change the fields within `::get_definition()`, you will need to be sure to update this constant so that table updates occur when they should.

#### `protected static $base_table_name`

This is the base name of the table without `$wpdb->prefix` applied. With the above example in mind, the value can be accessed using `PREFIX\Tables\Sandwiches::base_table_name()`. It is used for a couple of purposes:

* It is used to generate the name of the table in the `::get_description()` method.
* It is the index that is used in the `PREFIX\StellarWP\Schema\Tables\Collection` iterator - the class that collects and stores registered table schemas.
* It is used to associate field schemas to table schemas.

#### `protected static $group`

This value allows you to group table schemas together so you can do interesting things with them programmatically. There's no explicit use for groups out of the box other than providing some tooling for finding table schemas within a group.

Here's an example:

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema;

// This registers a the Sandwiches table that has `$group` set to `boom`.
Register::table( Tables\Sandwiches::class );

// Let's pretend there's a SomethingElse table schema that has `$group` set to `potato`.
Register::table( Tables\SomethingElse::class );

// This returns all of the table schemas that have `$group` set to `boom`.
$tables_in_group_boom = Schema::table_collection()->get_by_group( 'boom' );

foreach ( $tables_in_group_boom as $table_schema ) {
	echo $table_schema->get_sql() . "\n";
}
```

#### `protected static $schema_slug`

This is the slug of this table schema and is used to generate the `wp_options` key that is used to store the table schema version. Those options are in the following format: `stellarwp_schema_version_{$schema_slug}`.

#### `protected static $uid_column`

This is the name of the column that is used to uniquely identify rows within the table.

#### `protected function get_definition()`

This is the base definition of the table.

## Field schemas
