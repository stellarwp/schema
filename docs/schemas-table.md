# Table schemas

Table schema classes hold all of the building blocks for getting a custom table to be defined and managed by this library. Table schemas should have their base definition declared in the `::get_definition()` method, however, it is important to note that the final definition SQL (fetched via `::get_sql()`) can be influenced by registered Field Schemas.

Check out an [example table schema](schemas-table-example.md) and get a look at the minimally required properties and methods.

## Versioning

Table schema version numbers help this library determine whether a table needs to be updated. Each registered table will have an entry in the `wp_options` table that holds the version number of the table's schema definition. The `wp_option` key will follow the following format: `stellarwp_schema_version_{$base_table_name}`. The actual value of that option will be dependent on the schemas that are in use.

If you look at the example table schema above there is a `const SCHEMA_VERSION`. This should hold the base version number table definition. As the table's definition changes over time, you should make a point to update that version number accordingly. We highly recommend using semantic versioning for that number. It is important to note that the final version number for the table schema will be influenced by any [field schemas](schemas-field.md) that are registered for the table. If there are any, the field schema version numbers will be concatenated together and will be hashed using `md5()`.

So, let's say you have the following:

* A table schema with a version number of `1.0.0`.
* A field schema with a schema slug of `boom-field` and a version number of `1.0.0`.

The resulting version number for the table schema will be `1.0.0-612952b60dd79e4a3b336e212ed680db`. _(the hash is an `md5()` of `boom-field-1.0.0`)_

## Registering tables

Have a table schema class? You'll want to register it so that the table gets created. Here's what happens when you register a table before `plugins_loaded` priority `1000` (the default):

1. The table schema is instantiated.
1. The table schema gets added to the `PREFIX\StellarWP\Schema\Tables\Collection`, which you can get via `PREFIX\StellarWP\Schema::tables()`.
1. At `plugins_loaded` priority `1000`, `PREFIX\StellarWP\Schema::builder()->up()` is called.
1. The builder will loop over all registered table schemas and fetch the composite version number for each.
1. An `md5()` hash of all of the table schema versions will be built and will be compared against a hash stored within a transient (if it exists).
1. If the hash is missing or mismatched, any missing tables will be created.
1. Any tables that are present but the version numbers are mismatched, the table schema's `::get_sql()` will be called.
1. `::get_sql()` will look for any registered field schemas and inject their `::get_sql()` into the table schema's definition (`::get_definition()`).
1. `dbDelta()` will be run using the resulting SQL.
1. `::after_update()` will be run for the table schema.
1. `::after_update()` will be run for any field schema attached to the table.
1. The `wp_option` for the table's version number will be updated.

If you register a table _after_ `plugins_loaded` priority `1000`, bullet 3 above will be skipped and everything will be executed in that moment.

### Registering a tables individually

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema\Register;

// Let's pretend that we have two table schema classes.
Register::table( Tables\Burritos::class );
Register::table( Tables\Sandwiches::class );
```

### Registering multiple tables

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema\Register;

// Let's pretend that we have two table schema classes.
Register::tables( [
	Tables\Burritos::class,
	Tables\Sandwiches::class,
] );
```

## Deregistering tables

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema\Register;

// Let's pretend that we have two table schema classes.
Register::remove_table( Tables\Burritos::class );
Register::remove_table( Tables\Sandwiches::class );
```

## Table collection

Once registered, table schemas will exist within a `PREFIX\StellarWP\Schema\Tables\Collection`. This class is an object that implements [Iterator](https://www.php.net/manual/en/class.iterator.php), [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php), and [Countable](https://www.php.net/manual/en/class.countable.php). It can be looped over, accessed like an array, or counted like an array.

Additionally, there are a couple of helper methods that allow you to quickly grab table schemas for a specific group or schemas that need updates.

### Example: loop over all tables

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema;

// Let's pretend that we have three table schema classes. Brick is in the group `not-food`. The other two are in the group `food`.
Register::tables( [
	Tables\Bricks::class,
	Tables\Burritos::class,
	Tables\Sandwiches::class,
] );

// Let's get all of the table schemas so we can loop over them.
// This will return an Iterator with Bricks, Burritos, and Sandwiches.
$table_schemas = Schema::tables();

foreach ( $table_schemas as $table_schema ) {
	echo $table_schema->get_sql() . "\n";
}
```

### Example: loop over tables in a specific group

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema;

// Let's pretend that we have three table schema classes. Brick is in the group `not-food`. The other two are in the group `food`.
Register::tables( [
	Tables\Brick::class,
	Tables\Burritos::class,
	Tables\Sandwiches::class,
] );

// Let's get the table schemas in the "food" group so we can loop over them.
// This will return an Iterator with just Burritos and Sandwiches.
$table_schemas = Schema::tables()->get_by_group( 'food' );

foreach ( $table_schemas as $table_schema ) {
	echo $table_schema->get_sql() . "\n";
}
```

### Example: getting all tables that need updates

```php
use Boom\Shakalaka\Tables;
use Boom\Shakalaka\StellarWP\Schema;

// Let's pretend that we have two table schema classes.
// The Burritos version number is `1.0.0` and matches what is in the database.
// The Sandwiches version number is `1.5.0` but `1.0.0` is what is stored in the database.
Register::tables( [
	Tables\Burritos::class,
	Tables\Sandwiches::class,
] );

// Let's get the table schemas that need updates.
// NOTE: once plugins_loaded, priority 1000 is reached, any tables needing updates will already be updated.
$table_schemas = Schema::tables()->get_tables_needing_updates();

foreach ( $table_schemas as $table_schema ) {
	echo $table_schema->get_sql() . "\n";
}
```

## Publicly accessible methods

## `::after_update()`

This method allows you to set some code to execute after a table schema has been created or updated. Typically, this method is used for running SQL that augments the table in some fashion. Here's an example:

```php
public function after_update() {
	// If nothing was changed by dbDelta(), bail.
	if ( ! count( $results ) ) {
		return $results;
	}

	global $wpdb;

	$table_name = static::table_name( true );
	$updated    = false;

	if ( $this->exists() && $this->has_index( 'boom' ) ) {
		$udpated = $wpdb->query( "ALTER TABLE `{$table_name}` ADD UNIQUE( `name` )" );
	}

	if ( $updated ) {
		$message = "Added UNIQUE constraint to the {$table_name} table on name.";
	} else {
		$message = "Failed to add a unique constraint on the {$table_name} table.";
	}

	$results[ $table_name . '.name' ] = $message;

	return $results;
}
```

## `::base_table_name()`

This method (called statically), returns the base table name for a table schema.

## `::drop()`

**Proceed with caution!** This method will drop the table and data will be lost.

## `::empty_table()`

**Proceed with caution!** This method will empty out the table.

## `::exists()`

Returns a boolean of whether or not the table exists.

## `::get_field_schemas( $force = false )`

Fetches a collection of all field schemas for the table.

## `::get_schema_slug()`

Gets the schema slug for the table.

## `::get_schema_version_option()`

Gets the `wp_option` key for the table schema version.

## `::get_sql()`

Gets the full SQL for the table with all of the relevant field schema SQL injected into it.

## `::group_name()`

Gets the group name for the table.

## `::is_schema_current()`

Returns a boolean of whether or not the table schema is current.

## `::sync_stored_version()`

Forces the stored version to be updated to the current version of the table schema.

## `::table_name( $with_prefix = true )`

Gets the table name for the table schema. If `true` is passed, it will include the `$wpdb->prefix`.

## `::uid_column()`

Gets the UID column for the table.

## `::update()`

Runs the update operation for the table schema. Meaning, it runs `dbDelta()` and the relevant `after_update()` methods for the table schema and all attached field schemas.
