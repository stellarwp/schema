# Column System (v3.0+)

Version 3.0.0 introduces a powerful type-safe column definition system. Instead of writing raw SQL, you can now define table columns using strongly-typed PHP classes that provide automatic type casting, validation, and a fluent API.

## Available Column Types

### Base Column Classes

- **`Integer_Column`** - For integer types (TINYINT, SMALLINT, MEDIUMINT, INT, BIGINT)
- **`Float_Column`** - For floating point types (FLOAT, DOUBLE, DECIMAL)
- **`String_Column`** - For fixed and variable length strings (CHAR, VARCHAR)
- **`Text_Column`** - For text types (TINYTEXT, TEXT, MEDIUMTEXT, LONGTEXT)
- **`Datetime_Column`** - For date and time types (DATE, DATETIME, TIMESTAMP)
- **`Boolean_Column`** - For boolean values (stored as TINYINT(1))
- **`Blob_Column`** - For binary data (TINYBLOB, BLOB, MEDIUMBLOB, LONGBLOB)
- **`Binary_Column`** - For fixed-length binary data (BINARY, VARBINARY)

### Specialized Column Classes

- **`ID`** - Pre-configured auto-incrementing primary key
- **`Referenced_ID`** - Foreign key reference to another table
- **`Created_At`** - Timestamp column with automatic CURRENT_TIMESTAMP default
- **`Updated_At`** - Timestamp column that updates on row changes
- **`Last_Changed`** - Alias for `Updated_At`

## Column Types Enum

Use the `Column_Types` class to specify MySQL column types:

```php
use StellarWP\Schema\Columns\Column_Types;

// Integer types
Column_Types::TINYINT
Column_Types::SMALLINT
Column_Types::MEDIUMINT
Column_Types::INT
Column_Types::BIGINT

// Float types
Column_Types::FLOAT
Column_Types::DOUBLE
Column_Types::DECIMAL

// String types
Column_Types::CHAR
Column_Types::VARCHAR

// Text types
Column_Types::TINYTEXT
Column_Types::TEXT
Column_Types::MEDIUMTEXT
Column_Types::LONGTEXT

// Date/Time types
Column_Types::DATE
Column_Types::DATETIME
Column_Types::TIMESTAMP

// Binary types
Column_Types::BINARY
Column_Types::VARBINARY
Column_Types::TINYBLOB
Column_Types::BLOB
Column_Types::MEDIUMBLOB
Column_Types::LONGBLOB

// Other types
Column_Types::BOOLEAN
Column_Types::JSON
```

## Common Column Methods

All column classes support a fluent API with these methods:

### Required Methods

```php
// Set the column name.
$column = new Integer_Column( 'user_id' );
```

### Type Configuration

```php
// Set the MySQL column type.
->set_type( Column_Types::INT )

// Set the length/size.
->set_length( 11 )

// Set precision for float columns (decimal places).
->set_precision( 2 )  // For DECIMAL(10,2).
```

### Nullability and Defaults

```php
// Allow NULL values (default is NOT NULL).
->set_nullable( true )

// Set a default value.
->set_default( 0 )
->set_default( 'pending' )
->set_default( 'CURRENT_TIMESTAMP' )  // MySQL function.
```

### Signing (Integer/Float only)

```php
// Set signed/unsigned (default varies by type).
->set_signed( false )  // UNSIGNED.
->set_signed( true )   // SIGNED.
```

### Auto Increment (Integer only)

```php
// Enable auto-increment.
->set_auto_increment( true )
```

### Indexes

```php
// Mark column as indexed.
->set_is_index( true )

// Mark column as unique.
->set_is_unique( true )

// Mark column as primary key.
->set_is_primary( true )
```

### Searchability

```php
// Mark column as searchable (used by query methods).
->set_searchable( true )
```

## PHP Type Mapping

Columns automatically map between PHP and MySQL types:

| Column Class | MySQL Type | PHP Type |
|-------------|-----------|---------|
| `Integer_Column` | INT, BIGINT, etc. | `int` |
| `Float_Column` | FLOAT, DOUBLE, DECIMAL | `float` |
| `String_Column` | VARCHAR, CHAR | `string` |
| `Text_Column` | TEXT, LONGTEXT, etc. | `string` |
| `Datetime_Column` | DATETIME, TIMESTAMP | `DateTimeInterface` |
| `Boolean_Column` | TINYINT(1) | `bool` |
| `Blob_Column` | BLOB | `string` (base64) |

## Examples

### Basic Integer Column

```php
$columns[] = ( new Integer_Column( 'user_id' ) )
	->set_type( Column_Types::BIGINT )
	->set_length( 20 )
	->set_signed( false );
```

### String Column with Default

```php
$columns[] = ( new String_Column( 'status' ) )
	->set_type( Column_Types::VARCHAR )
	->set_length( 50 )
	->set_default( 'pending' );
```

### Nullable Text Column

```php
$columns[] = ( new Text_Column( 'notes' ) )
	->set_type( Column_Types::TEXT )
	->set_nullable( true );
```

### Decimal Price Column

```php
$columns[] = ( new Float_Column( 'price' ) )
	->set_type( Column_Types::DECIMAL )
	->set_length( 10 )  // Total digits
	->set_precision( 2 )  // Decimal places
	->set_default( 0.00 );
```

### Auto-incrementing ID

```php
$columns[] = ( new ID( 'id' ) )
	->set_length( 11 )
	->set_type( Column_Types::BIGINT )
	->set_auto_increment( true );
```

### Timestamp Columns

```php
// Created timestamp
$columns[] = new Created_At( 'created_at' );

// Updated timestamp
$columns[] = new Updated_At( 'updated_at' );
```

### Searchable Column

```php
$columns[] = ( new String_Column( 'title' ) )
	->set_type( Column_Types::VARCHAR )
	->set_length( 200 )
	->set_searchable( true );
```

### Foreign Key Reference

```php
$columns[] = ( new Referenced_ID( 'event_id' ) )
	->set_length( 11 )
	->set_type( Column_Types::BIGINT );
```

### Boolean Column

```php
$columns[] = ( new Boolean_Column( 'is_active' ) )
	->set_default( true );
```

### JSON Column

```php
$columns[] = ( new Text_Column( 'metadata' ) )
	->set_type( Column_Types::JSON )
	->set_nullable( true );
```

## Complete Example

```php
public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			$columns = new Column_Collection();

			// Primary key.
			$columns[] = ( new ID( 'id' ) )
				->set_length( 11 )
				->set_auto_increment( true );

			// Foreign key.
			$columns[] = ( new Referenced_ID( 'user_id' ) )
				->set_length( 11 )
				->set_type( Column_Types::BIGINT );

			// String fields.
			$columns[] = ( new String_Column( 'title' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 255 )
				->set_searchable( true );

			$columns[] = ( new String_Column( 'status' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 20 )
				->set_default( 'draft' )
				->set_is_index( true );

			// Text field.
			$columns[] = ( new Text_Column( 'content' ) )
				->set_type( Column_Types::LONGTEXT )
				->set_searchable( true );

			// Numeric fields.
			$columns[] = ( new Integer_Column( 'views' ) )
				->set_type( Column_Types::INT )
				->set_default( 0 );

			$columns[] = ( new Float_Column( 'rating' ) )
				->set_type( Column_Types::DECIMAL )
				->set_length( 3 )
				->set_precision( 2 )
				->set_default( 0.00 );

			// Boolean.
			$columns[] = ( new Boolean_Column( 'is_published' ) )
				->set_default( false );

			// Timestamps.
			$columns[] = new Created_At( 'created_at' );
			$columns[] = new Updated_At( 'updated_at' );

			return new Table_Schema( $table_name, $columns );
		},
	];
}
```

## Benefits of the Column System

1. **Type Safety**: Catch errors at development time instead of runtime
2. **Auto Type Casting**: Values are automatically converted between PHP and MySQL types
3. **Fluent API**: Readable, chainable method calls
4. **Validation**: Column definitions are validated when the table is created
5. **IDE Support**: Full autocomplete and type hints
6. **Query Methods**: Enables built-in CRUD operations on your tables
7. **Searchability**: Mark columns as searchable for automatic full-text search
8. **Migration Friendly**: Easy to version and track schema changes

## See Also

- [Index System](indexes.md)
- [Table Schemas](schemas-table.md)
- [Query Methods](query-methods.md)
- [Migrating from v2 to v3](migrating-from-v2-to-v3.md)