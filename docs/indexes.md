# Index System (v3.0+)

Version 3.0.0 introduces a type-safe index management system. Define indexes using dedicated PHP classes instead of raw SQL for better type safety and validation.

## Available Index Types

### `Primary_Key`

Defines the primary key for the table. Primary keys uniquely identify each row and must contain non-NULL values.

```php
use StellarWP\Schema\Indexes\Primary_Key;

// Single column primary key.
$indexes[] = new Primary_Key( [ 'id' ] );

// Composite primary key.
$indexes[] = new Primary_Key( [ 'event_id', 'ticket_id' ] );
```

### `Unique_Key`

Ensures that all values in the indexed columns are unique across the table.

```php
use StellarWP\Schema\Indexes\Unique_Key;

// Single column unique constraint.
$indexes[] = new Unique_Key( [ 'email' ] );

// Composite unique constraint.
$indexes[] = new Unique_Key( [ 'user_id', 'event_id' ] );
```

### `Classic_Index`

A standard index that speeds up lookups on the specified columns. Does not enforce uniqueness.

```php
use StellarWP\Schema\Indexes\Classic_Index;

// Single column index.
$indexes[] = new Classic_Index( [ 'status' ] );

// Composite index.
$indexes[] = new Classic_Index( [ 'user_id', 'created_at' ] );
```

### `Fulltext_Index`

Enables full-text searching on text columns. Useful for content search functionality.

```php
use StellarWP\Schema\Indexes\Fulltext_Index;

// Single column fulltext.
$indexes[] = new Fulltext_Index( [ 'content' ] );

// Multiple columns fulltext.
$indexes[] = new Fulltext_Index( [ 'title', 'content', 'excerpt' ] );
```

## Creating Indexes

Indexes are defined within the `get_schema_history()` method and added to an `Index_Collection`:

```php
use StellarWP\Schema\Collections\Index_Collection;

public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			$columns = new Column_Collection();

			// ... define columns ...

			// Define indexes
			$indexes = new Index_Collection();

			$indexes[] = new Primary_Key( [ 'id' ] );
			$indexes[] = new Classic_Index( [ 'status' ] );
			$indexes[] = new Unique_Key( [ 'email' ] );

			return new Table_Schema( $table_name, $columns, $indexes );
		},
	];
}
```

## Index Naming

The library automatically generates index names based on the index type and columns:

- **Primary Key**: `PRIMARY`
- **Unique Key**: `unique_{column1}_{column2}`
- **Classic Index**: `index_{column1}_{column2}`
- **Fulltext Index**: `fulltext_{column1}_{column2}`

## Composite Indexes

All index types support multiple columns (composite indexes):

```php
// Composite classic index.
$indexes[] = new Classic_Index( [ 'user_id', 'status', 'created_at' ] );

// Composite unique key.
$indexes[] = new Unique_Key( [ 'external_id', 'source' ] );
```

**Important**: The order of columns in a composite index matters for query optimization. Place the most selective columns first.

## Examples

### E-commerce Orders Table

```php
public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			$columns = new Column_Collection();

			$columns[] = ( new ID( 'id' ) )
				->set_auto_increment( true );

			$columns[] = ( new String_Column( 'order_number' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 50 );

			$columns[] = ( new Referenced_ID( 'customer_id' ) )
				->set_type( Column_Types::BIGINT );

			$columns[] = ( new String_Column( 'status' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 20 );

			$columns[] = ( new Float_Column( 'total' ) )
				->set_type( Column_Types::DECIMAL )
				->set_length( 10 )
				->set_precision( 2 );

			$columns[] = new Created_At( 'created_at' );

			// Indexes for common queries.
			$indexes = new Index_Collection();

			// Unique order numbers.
			$indexes[] = new Unique_Key( [ 'order_number' ] );

			// Fast lookups by customer.
			$indexes[] = new Classic_Index( [ 'customer_id' ] );

			// Fast lookups by status.
			$indexes[] = new Classic_Index( [ 'status' ] );

			// Efficient date range queries.
			$indexes[] = new Classic_Index( [ 'created_at' ] );

			// Composite index for customer orders by status.
			$indexes[] = new Classic_Index( [ 'customer_id', 'status' ] );

			return new Table_Schema( $table_name, $columns, $indexes );
		},
	];
}
```

### Blog Posts with Fulltext Search

```php
public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			$columns = new Column_Collection();

			$columns[] = ( new ID( 'id' ) )
				->set_auto_increment( true );

			$columns[] = ( new String_Column( 'title' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 255 );

			$columns[] = ( new Text_Column( 'content' ) )
				->set_type( Column_Types::LONGTEXT );

			$columns[] = ( new String_Column( 'slug' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 255 );

			$columns[] = ( new String_Column( 'status' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 20 );

			$indexes = new Index_Collection();

			// Unique slugs for URL routing.
			$indexes[] = new Unique_Key( [ 'slug' ] );

			// Full-text search on title and content.
			$indexes[] = new Fulltext_Index( [ 'title', 'content' ] );

			// Fast filtering by status.
			$indexes[] = new Classic_Index( [ 'status' ] );

			return new Table_Schema( $table_name, $columns, $indexes );
		},
	];
}
```

### User Sessions Table

```php
public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			$columns = new Column_Collection();

			// Using session_id as primary key (not auto-increment).
			$columns[] = ( new String_Column( 'session_id' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 128 );

			$columns[] = ( new Referenced_ID( 'user_id' ) )
				->set_type( Column_Types::BIGINT )
				->set_nullable( true );

			$columns[] = ( new Text_Column( 'data' ) )
				->set_type( Column_Types::TEXT );

			$columns[] = ( new Datetime_Column( 'expires_at' ) )
				->set_type( Column_Types::DATETIME );

			$indexes = new Index_Collection();

			// String-based primary key.
			$indexes[] = new Primary_Key( [ 'session_id' ] );

			// Lookup sessions by user.
			$indexes[] = new Classic_Index( [ 'user_id' ] );

			// Efficiently clean up expired sessions.
			$indexes[] = new Classic_Index( [ 'expires_at' ] );

			return new Table_Schema( $table_name, $columns, $indexes );
		},
	];
}
```

### Many-to-Many Relationship Table

```php
public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			$columns = new Column_Collection();

			$columns[] = ( new Referenced_ID( 'event_id' ) )
				->set_type( Column_Types::BIGINT );

			$columns[] = ( new Referenced_ID( 'venue_id' ) )
				->set_type( Column_Types::BIGINT );

			$columns[] = new Created_At( 'created_at' );

			$indexes = new Index_Collection();

			// Composite primary key (no auto-increment needed).
			$indexes[] = new Primary_Key( [ 'event_id', 'venue_id' ] );

			// Fast reverse lookups.
			$indexes[] = new Classic_Index( [ 'venue_id' ] );

			return new Table_Schema( $table_name, $columns, $indexes );
		},
	];
}
```

## Index Best Practices

### When to Use Indexes

✅ **DO** add indexes for:
- Primary keys (required)
- Foreign keys used in JOINs
- Columns used in WHERE clauses
- Columns used in ORDER BY
- Unique constraints (email, username, etc.)
- Full-text search columns

❌ **DON'T** add indexes for:
- Small tables (< 1000 rows)
- Columns with low cardinality (few unique values)
- Columns that are rarely queried
- Every column (over-indexing slows down writes)

### Composite Index Tips

1. **Order matters**: Put the most selective column first
2. **Leftmost prefix**: MySQL can use partial composite indexes from left to right
3. **Cover common queries**: Design indexes around your most frequent query patterns

```php
// Good: User queries often filter by status first, then date.
$indexes[] = new Classic_Index( [ 'status', 'created_at' ] );

// Also good: Allows queries on just 'status' OR 'status' + 'created_at'.
// MySQL can use the leftmost prefix.
```

### Performance Considerations

- **Read vs Write**: Indexes speed up reads but slow down writes
- **Index size**: More indexes = more disk space and memory usage
- **Maintenance**: Indexes need to be rebuilt/optimized periodically

## Migration Example

Adding an index to an existing table:

```php
public static function get_schema_history(): array {
	$table_name = static::table_name( true );

	return [
		'1.0.0' => function() use ( $table_name ) {
			// Original schema.
			$columns = new Column_Collection();
			$columns[] = ( new ID( 'id' ) )->set_auto_increment( true );
			$columns[] = ( new String_Column( 'email' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 255 );

			return new Table_Schema( $table_name, $columns );
		},
		'1.1.0' => function() use ( $table_name ) {
			// Add unique constraint on email in version 1.1.0.
			$columns = new Column_Collection();
			$columns[] = ( new ID( 'id' ) )->set_auto_increment( true );
			$columns[] = ( new String_Column( 'email' ) )
				->set_type( Column_Types::VARCHAR )
				->set_length( 255 );

			$indexes = new Index_Collection();
			$indexes[] = new Unique_Key( [ 'email' ] );

			return new Table_Schema( $table_name, $columns, $indexes );
		},
	];
}
```

## See Also

- [Column System](columns.md)
- [Table Schemas](schemas-table.md)
- [Query Methods](query-methods.md)
- [Migrating from v2 to v3](migrating-from-v2-to-v3.md)