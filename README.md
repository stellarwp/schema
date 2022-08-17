# Schema Builder

[![CI](https://github.com/stellarwp/schema/workflows/CI/badge.svg)](https://github.com/stellarwp/schema/actions?query=branch%3Amain) [![Static Analysis](https://github.com/stellarwp/schema/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/stellarwp/schema/actions/workflows/static-analysis.yml)

A library for simplifying the creation, update, and field modification of custom tables within WordPress.

## Installation

It's recommended that you install the Schema as a project dependency via [Composer](https://getcomposer.org/):

```bash
composer require stellarwp/schema
```

### Strauss

Including this library into your project as-is should be avoided. Instead, we recommend using [Strauss](https://github.com/BrianHenryIE/strauss) to ensure this library is namespaced for your project. This way, if your WordPress project is run alongside another WordPress project that uses this library, the two won't conflict!

Adding Strauss to your `composer.json` is only slightly more complicated than adding a typical dependency, so checkout our [strauss docs](docs/strauss-setup.md).

## Important note on ALL examples

All examples within the documentation for this project will be assuming that you are using [Strauss](#strauss) to prefix the namespaces provided by this library.

The examples will be using:

* `Boom\Shakalaka\` as the namespace prefix
* `BOOM_SHAKALAKA_` as the constant prefix.

## Getting started

For a full understanding of what is available in this library and how to use it, definitely read through the full [documentation](#documentation). But for folks that want to get rolling with the basics quickly, try out the following.

### Creating a table

Let's say you want a new custom table called `sandwiches` (with the default WP prefix, it'd be `wp_sandwiches`). You'll need a class file for the table. For the sake of this example, we'll be assuming this class is going into a `Tables/` directory and is reachable via the `Boom\Shakalaka\Tables` namespace.

```php
<?php
namespace Boom\Shakalaka\Tables;

use Boom\Shakalaka\StellarWP\Schema\Builder;

class Sandwiches extends Builder\Abstract_Custom_Table {
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
	public function get_sql() {
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
* `get_sql()`: This should return the SQL used to create your `sandwiches` table.

### Initializing the Schema library and registering the table

Within the `plugins_loaded` action, you'll need to initialize the Schema library and register the table using the handy `Register` class:

```php
namespace Boom\Shakalaka;

use Boom\Shakalaka\StellarWP\Schema\Register;
use Boom\Shakalaka\StellarWP\Schema\Schema;
use Boom\Shakalaka\Tables\Sandwiches;

add_action( 'plugins_loaded', static function() {
	Schema::init();
	Register::table( Sandwiches::class );
} );
```

### That's it!

The table will be automatically registered, created, and updated during the `plugins_loaded` action at priority `1000`! _(that priority number is filterable via the `stellarwp_schema_up_plugins_loaded_priority` filter)_

## Documentation

Here's some more advanced documentation to get you rolling on using this library at a deeper level:

1. [Setting up Strauss](docs/strauss-setup.md)
1. [Table schemas](docs/table-schema.md)
1. [Field schemas](docs/field-schema.md)
1. [Schema versioning](docs/schema-versioning.md)
1. [Registering and de-registering schemas](docs/registering-and-deregistering.md)
1. [Hooks](docs/hooks.md)
