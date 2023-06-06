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

### Creating a table

Let's say you want a new custom table called `sandwiches` (with the default WP prefix, it'd be `wp_sandwiches`). You'll need a class file for the table. For the sake of this example, we'll be assuming this class is going into a `Tables/` directory and is reachable via the `Boom\Shakalaka\Tables` namespace.

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

Here's what the properties and method mean:

* `$base_table_name`: The name of the table without the prefix.
* `$group`: The group that the table belongs to - this is for organizational purposes.
* `$schema_slug`: An identifier for the table. This is used in storing your table's schema version in `wp_options`.
* `$uid_column`: The name of the column that is used to uniquely identify each row.
* `get_definition()`: This should return the base SQL definition used to create your `sandwiches` table. To get the full SQL (with any field schemas included), you can call `get_sql()`!

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
1. [Automated testing](/docs/automated-testing.md)

## Acknowledgements

Special props go to [@lucatume](https://github.com/lucatume) and [@stratease](https://github.com/stratease) for their initial work on this structure before it was extracted into a standalone library.
