# Field schemas

Table schema classes hold all of the building blocks for getting a custom table to be defined and managed by this library. Table schemas should have their base definition declared in the `::get_definition()` method, however, it is important to note that the final definition SQL (fetched via `::get_sql()`) can be influenced by registered Field Schemas.

Check out an [example field schema](schemas-field-example.md) and get a look at the minimally required properties and methods.

## Versioning

Field schema versions are used to augment the resulting version number of the table schema to which the field schema is associated. For more details around how table schema versions are built and stored, see the documentation for [table schemas](schemas-table.md).

## Registering fields

Field schemas need to be registered for them to be implemented. Additionally, all field schemas _must_ have a corresponding table schema that is registered. To associate a field schema with a table schema, the `base_table_name` property of both the field schema and the table schema must match. _(Check ou the [example field schema](schemas-field-example.md) and the [example table schema](schemas-table-example.md) to see what we mean.)_

Anyhow, here's what happens when a field schema gets registered.

1. The field schema is instantiated.
1. The field schema gets added to the `PREFIX\StellarWP\Schema\Fields\Collection`, which you can get via `PREFIX\StellarWP\Schema::fields()`.
1. _... read check the [table schema](schemas-table.md#registering-fields) "Registering fields" section._

If you register a field schema _after_ `plugins_loaded` priority `1000`, everything will be executed in that moment rather than waiting for a future WP action.

### Registering a fields individually

```php
use Boom\Shakalaka\Fields;
use Boom\Shakalaka\StellarWP\Schema\Register;
use Boom\Shakalaka\Tables;

// Let's pretend that we have two table schema classes.
Register::table( Tables\Burritos::class );
Register::table( Tables\Sandwiches::class );

// Let's pretend that we have two field schema classes.
Register::field( Fields\BurritosPro::class );
Register::field( Fields\SandwichesPro::class );
```

### Registering multiple fields

```php
use Boom\Shakalaka\Fields;
use Boom\Shakalaka\StellarWP\Schema\Register;
use Boom\Shakalaka\Tables;

// Let's pretend that we have two table schema classes.
Register::table( Tables\Burritos::class );
Register::table( Tables\Sandwiches::class );

// Let's pretend that we have two field schema classes.
Register::fields( [
	Fields\BurritosPro::class,
	Fields\SandwichesPro::class,
] );
```

## Deregistering fields

```php
use Boom\Shakalaka\Fields;
use Boom\Shakalaka\StellarWP\Schema\Register;

// Let's pretend that we have two field schema classes.
Register::remove_field( Fields\Burritos::class );
Register::remove_field( Fields\Sandwiches::class );
```

## Field collection

Once registered, field schemas will exist within a `PREFIX\StellarWP\Schema\Fields\Collection`. This class is an object that implements [Iterator](https://www.php.net/manual/en/class.iterator.php), [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php), and [Countable](https://www.php.net/manual/en/class.countable.php). It can be looped over, accessed like an array, or counted like an array.

Additionally, there is a helper method that allow you to quickly field schemas that impact a specific table schema.

### Example: loop over all fields

```php
use Boom\Shakalaka\Fields;
use Boom\Shakalaka\StellarWP\Schema;
use Boom\Shakalaka\Tables;

// Let's pretend that we have three table schema classes. Brick is in the group `not-food`. The other two are in the group `food`.
Register::tables( [
	Tables\Bricks::class,
	Tables\Burritos::class,
	Tables\Sandwiches::class,
] );

// Let's pretend that we have two field schema classes.
Register::fields( [
	Fields\BurritosPro::class,
	Fields\SandwichesPro::class,
] );

// Let's get all of the field schemas so we can loop over them.
// This will return an Iterator with BurritosPro and SandwichesPro.
$field_schemas = Schema::fields();

foreach ( $field_schemas as $field_schema ) {
	echo $field_schema->get_schema_slug() . "\n";
}
```

### Example: loop over tables in a specific group

```php
use Boom\Shakalaka\Fields;
use Boom\Shakalaka\StellarWP\Schema;
use Boom\Shakalaka\Tables;

// Let's pretend that we have three table schema classes. Brick is in the group `not-food`. The other two are in the group `food`.
Register::tables( [
	Tables\Bricks::class,
	Tables\Burritos::class,
	Tables\Sandwiches::class,
] );

// Let's pretend that we have two field schema classes.
Register::fields( [
	Fields\BurritosPro::class,
	Fields\SandwichesPro::class,
] );

// Let's get the field schemas attached to the "sandwiches" table so we can loop over them.
// This will return an Iterator with just SandwichesPro.
$field_schemas = Schema::fields()->get_by_table( 'sandwiches' );

foreach ( $field_schemas as $field_schema ) {
	echo $field_schema->get_schema_slug() . "\n";
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

**Proceed with caution!** This method will drop the fields from the table and data will be lost.

## `::exists()`

Returns a boolean of whether or not the columns exist in the table.

## `::get_fields()`

Returns the array of fields that are a part of this field schema.

## `::get_schema_slug()`

Gets the schema slug for the field schema.

## `::get_sql()`

Gets the full SQL for the table with all of the relevant field schema SQL injected into it.

## `::get_version()`

Gets the field schema's version, a combination of the `::$schema_slug` and `const SCHEMA_VERSION`.

## `::group_name()`

Gets the group name for the table.

## `::table_schema()`

Gets the table schema from the `PREFIX\StellarWP\Schema::tables()` collection.
