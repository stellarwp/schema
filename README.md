# Schema Builder

[![CI](https://github.com/stellarwp/schema/workflows/CI/badge.svg)](https://github.com/stellarwp/schema/actions?query=branch%3Amain) [![Static Analysis](https://github.com/stellarwp/schema/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/stellarwp/schema/actions/workflows/static-analysis.yml)

A library for simplifying the creation, update, and field modification of custom tables within WordPress.

## Installation

It's recommended that you install Schema as a project dependency via [Composer](https://getcomposer.org/):

```bash
composer require stellarwp/schema
```

> We _actually_ recommend that this library gets included in your project using [Strauss](https://github.com/BrianHenryIE/strauss).
>
> Luckily, adding Strauss to your `composer.json` is only slightly more complicated than adding a typical dependency, so checkout our [strauss docs](https://github.com/stellarwp/global-docs/blob/main/docs/strauss-setup.md).

## Usage prerequisites

To actually _use_ the schema library, you must have two additional libraries in your project:

1. A Dependency Injection Container (DI Container) that is compatible with [di52](https://github.com/lucatume/di52) _(We recommend using di52.)_.
1. The [stellarwp/db](https://github.com/stellarwp/db) library.

In order to keep this library as light as possible, those dependencies are not included in the library itself. To avoid version compatibility issues, those libraries are _also_ not included as Composer dependencies. Instead, you must include them in your project. We recommend including them via composer [using Strauss](https://github.com/stellarwp/global-docs/blob/main/docs/strauss-setup.md), just like you have done with this library.

## Important note on ALL examples

All examples within the documentation for this project will be assuming that you are using [Strauss](#strauss) to prefix the namespaces provided by this library.

The examples will be using:

* `Boom\Shakalaka\` as the namespace prefix, though will sometimes be referenced as `PREFIX\` for the purpose of brevity in the docs.
* `BOOM_SHAKALAKA_` as the constant prefix.

## What's new in 3.0.0

Version 3.0.0 introduces major new features and breaking changes:

- **Type-safe Column Definitions**: Define table columns using strongly-typed classes (`Integer_Column`, `String_Column`, `Float_Column`, etc.) instead of raw SQL
- **Index Management**: Create and manage indexes with dedicated classes (`Primary_Key`, `Unique_Key`, `Classic_Index`, `Fulltext_Index`)
- **Schema History**: Track and manage schema changes over time with the `get_schema_history()` method
- **Enhanced Query Methods**: Access built-in CRUD operations through the `Custom_Table_Query_Methods` trait
- **Improved Type Safety**: Automatic type casting and validation for PHP/MySQL data transformation

**Note**: Version 3.0.0 is NOT backwards compatible with 2.x. See the [migration guide](docs/migrating-from-v2-to-v3.md) for upgrading from v2 to v3.

## Getting started

For a full understanding of what is available in this library and how to use it, definitely read through the full [documentation](#documentation). But for folks that want to get rolling with the basics quickly, try out the following.

### Initializing the library

```php
use Boom\Shakalaka\StellarWP\Schema\Config;

// You'll need a Dependency Injection Container that is compatible with https://github.com/lucatume/di52.
use Boom\Shakalaka\lucatume\DI52\Container;

// You'll need to use the StellarWP\DB library for database operations.
use Boom\Shakalaka\StellarWP\DB\DB;

$container = new Boom\Shakalaka\lucatume\DI52\Container();

Config::set_container( $container );
Config::set_db( DB::class );
```

### Creating a table (v3.0+)

Let's say you want a new custom table called `sandwiches` (with the default WP prefix, it'd be `wp_sandwiches`). You'll need a class file for the table. For the sake of this example, we'll be assuming this class is going into a `Tables/` directory and is reachable via the `Boom\Shakalaka\Tables` namespace.

**Version 3.0** introduces a new, type-safe way to define tables using Column and Index classes:

```php
<?php
namespace Boom\Shakalaka\Tables;

use Boom\Shakalaka\StellarWP\Schema\Tables\Contracts\Table;
use Boom\Shakalaka\StellarWP\Schema\Tables\Table_Schema;
use Boom\Shakalaka\StellarWP\Schema\Collections\Column_Collection;
use Boom\Shakalaka\StellarWP\Schema\Columns\ID;
use Boom\Shakalaka\StellarWP\Schema\Columns\String_Column;
use Boom\Shakalaka\StellarWP\Schema\Columns\Column_Types;

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
	protected static $group = 'boom';

	/**
	 * {@inheritdoc}
	 */
	protected static $schema_slug = 'boom-sandwiches';

	/**
	 * {@inheritdoc}
	 */
	public static function get_schema_history(): array {
		$table_name = static::table_name( true );

		return [
			'1.0.0' => function() use ( $table_name ) {
				$columns = new Column_Collection();

				// Define an auto-incrementing ID column
				$columns[] = ( new ID( 'id' ) )
					->set_length( 11 )
					->set_type( Column_Types::INT )
					->set_auto_increment( true );

				// Define a varchar column for the name
				$columns[] = ( new String_Column( 'name' ) )
					->set_type( Column_Types::VARCHAR )
					->set_length( 50 );

				return new Table_Schema( $table_name, $columns );
			},
		];
	}
}
```

You can still use the `get_definition()` method for backwards compatibility, but the new Column/Index system is recommended for all new tables. You must switch the visibility of the `get_definition()` method to `public` and still implement the `get_schema_history()` method, however.

Here's what the properties and method mean:

* `$base_table_name`: The name of the table without the prefix.
* `$group`: The group that the table belongs to - this is for organizational purposes.
* `$schema_slug`: An identifier for the table. This is used in storing your table's schema version in `wp_options`.
* `get_schema_history()`: Returns an array of callables keyed by version number. Each callable returns a `Table_Schema` object defining columns and indexes for that version.

**Key features of the new v3 system:**

* **Type-safe columns**: Use `Integer_Column`, `String_Column`, `Float_Column`, `Text_Column`, `Datetime_Column`, and specialized columns like `ID`, `Created_At`, `Updated_At`
* **Fluent API**: Chain methods like `set_length()`, `set_default()`, `set_nullable()`, `set_auto_increment()`
* **Index support**: Define indexes with `Classic_Index`, `Unique_Key`, `Primary_Key`, `Fulltext_Index`
* **Automatic type casting**: Values are automatically cast between PHP and MySQL types

### Registering the table

The Schema library gets initialized automatically when you register at table or a field schema. To register your table, simply use the handy `Register::table()` method within the `plugins_loaded`:

```php
namespace Boom\Shakalaka;

use Boom\Shakalaka\StellarWP\Schema\Register;
use Boom\Shakalaka\Tables\Sandwiches;

add_action( 'plugins_loaded', static function() {
	Register::table( Sandwiches::class );
} );
```

### That's it!

The table will be automatically registered, created, and updated during the `plugins_loaded` action at priority `1000`! _(that priority number is filterable via the `stellarwp_schema_up_plugins_loaded_priority` filter)_

## Documentation

Here's some more advanced documentation to get you rolling on using this library at a deeper level:

1. [Setting up Strauss](/docs/strauss-setup.md)
1. [Schema management](/docs/schemas.md)
1. [Table schemas](/docs/schemas-table.md)
	1. [Versioning](/docs/schemas-table.md#versioning)
	1. [Registering tables](/docs/schemas-table.md#registering-tables)
	1. [Deregistering tables](/docs/schemas-table.md#deregistering-tables)
	1. [Table collection](/docs/schemas-table.md#table-collection)
	1. [Publicly accessible methods](/docs/schemas-table.md#publicly-accessible-methods)
1. [Field schemas](/docs/schemas-field.md)
	1. [Versioning](/docs/schemas-field.md#versioning)
	1. [Registering fields](/docs/schemas-field.md#registering-field)
	1. [Deregistering fields](/docs/schemas-field.md#deregistering-fields)
	1. [Field collection](/docs/schemas-field.md#field-collection)
	1. [Publicly accessible methods](/docs/schemas-field.md#publicly-accessible-methods)
1. [Migrating from v2 to v3](/docs/migrating-from-v2-to-v3.md) - **Important for existing users!**
1. [Automated testing](/docs/automated-testing.md)

## Acknowledgements

Special props go to [@lucatume](https://github.com/lucatume) and [@stratease](https://github.com/stratease) for their initial work on this structure before it was extracted into a standalone library.
