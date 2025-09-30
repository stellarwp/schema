# Query Methods (v3.0+)

Version 3.0.0 introduces built-in CRUD (Create, Read, Update, Delete) operations through the `Custom_Table_Query_Methods` trait. When you define tables using the new Column system, you automatically get access to these powerful query methods with automatic type casting and validation.

## Overview

All query methods:
- **Type-safe**: Automatically cast values between PHP and MySQL types
- **Validated**: Ensure column names exist and values are appropriate
- **Convenient**: No need to write raw SQL for common operations
- **Batched**: Support bulk operations where appropriate

## Insert Operations

### `::insert( array $entry )`

Insert a single row into the table.

**Parameters:**
- `$entry` - Associative array of column => value pairs

**Returns:** Number of rows affected, or `false` on failure

**Example:**
```php
$result = Sandwiches::insert( [
	'name'        => 'Club Sandwich',
	'type'        => 'classic',
	'price_cents' => 1299,
	'is_active'   => true,
] );

if ( $result ) {
	// Success - $result contains the insert ID for auto-increment columns
}
```

### `::insert_many( array $entries )`

Insert multiple rows in a single query (more efficient than multiple insert calls).

**Parameters:**
- `$entries` - Array of associative arrays

**Returns:** Number of rows affected, or `false` on failure

**Example:**
```php
$result = Sandwiches::insert_many( [
	[
		'name'        => 'BLT',
		'type'        => 'classic',
		'price_cents' => 899,
	],
	[
		'name'        => 'Reuben',
		'type'        => 'hot',
		'price_cents' => 1099,
	],
	[
		'name'        => 'Veggie Wrap',
		'type'        => 'healthy',
		'price_cents' => 799,
	],
] );
```

## Update Operations

### `::update_single( array $entry )`

Update a single row. The entry must include the primary key column(s).

**Parameters:**
- `$entry` - Associative array including primary key and fields to update

**Returns:** `true` on success, `false` on failure

**Example:**
```php
$result = Sandwiches::update_single( [
	'id'          => 42,
	'price_cents' => 1499,  // Increase price
	'updated_at'  => new DateTime(),
] );
```

### `::update_many( array $entries )`

Update multiple rows in a transaction. All updates succeed or all fail.

**Parameters:**
- `$entries` - Array of associative arrays, each including primary key

**Returns:** `true` if all updates succeeded, `false` if any failed

**Example:**
```php
$result = Sandwiches::update_many( [
	[ 'id' => 1, 'is_active' => true ],
	[ 'id' => 2, 'is_active' => true ],
	[ 'id' => 3, 'is_active' => false ],
] );
```

### `::upsert( array $entry )`

Insert a new row or update existing based on primary key presence.

**Parameters:**
- `$entry` - Associative array (with or without primary key)

**Returns:** `true` on success, `false` on failure

**Example:**
```php
// Will insert if no ID, update if ID exists
$result = Sandwiches::upsert( [
	'id'          => $maybe_existing_id ?? null,
	'name'        => 'Turkey Club',
	'price_cents' => 1199,
] );
```

## Delete Operations

### `::delete( int|string $uid, string $column = '' )`

Delete a single row by its unique identifier.

**Parameters:**
- `$uid` - The ID value
- `$column` - Optional column name (defaults to primary key)

**Returns:** `true` on success, `false` on failure

**Example:**
```php
// Delete by primary key
$result = Sandwiches::delete( 42 );

// Delete by custom column
$result = Sandwiches::delete( 'abc123', 'external_id' );
```

### `::delete_many( array $ids, string $column = '', string $more_where = '' )`

Delete multiple rows in a single query.

**Parameters:**
- `$ids` - Array of ID values
- `$column` - Optional column name (defaults to primary key)
- `$more_where` - Additional WHERE conditions

**Returns:** Number of rows deleted, or `false` on failure

**Example:**
```php
// Delete multiple sandwiches
$result = Sandwiches::delete_many( [ 1, 5, 12, 15 ] );

// Delete with additional condition
$result = Sandwiches::delete_many(
	[ 1, 2, 3 ],
	'id',
	'AND is_active = 0'
);
```

## Read Operations

### `::get_by_id( int|string $id )`

Fetch a single row by its primary key.

**Returns:** Object/array representing the row, or `null` if not found

**Example:**
```php
$sandwich = Sandwiches::get_by_id( 42 );

if ( $sandwich ) {
	echo $sandwich['name'];  // Values are automatically type-cast
	echo $sandwich['price_cents'];  // int
	echo $sandwich['is_active'];  // bool
}
```

### `::get_first_by( string $column, mixed $value )`

Fetch the first row matching a column value.

**Parameters:**
- `$column` - Column name to search
- `$value` - Value to match

**Returns:** Row data or `null` if not found

**Example:**
```php
$sandwich = Sandwiches::get_first_by( 'name', 'Club Sandwich' );
$sandwich = Sandwiches::get_first_by( 'type', 'classic' );
```

### `::get_all_by( string $column, mixed $value, string $operator = '=', int $limit = 50 )`

Fetch all rows matching a column value.

**Parameters:**
- `$column` - Column name
- `$value` - Value to match
- `$operator` - Comparison operator (=, !=, >, <, >=, <=, IN, NOT IN)
- `$limit` - Maximum rows to return

**Returns:** Array of rows

**Example:**
```php
// Get all classic sandwiches
$classics = Sandwiches::get_all_by( 'type', 'classic' );

// Get all sandwiches under $10
$affordable = Sandwiches::get_all_by( 'price_cents', 1000, '<' );

// Get specific sandwiches
$selection = Sandwiches::get_all_by( 'id', [ 1, 5, 10 ], 'IN' );
```

### `::get_all( int $batch_size = 50, string $where_clause = '', string $order_by = '' )`

Generator that yields all rows in batches. Memory efficient for large datasets.

**Parameters:**
- `$batch_size` - Number of rows per batch
- `$where_clause` - Optional WHERE clause
- `$order_by` - Optional ORDER BY clause

**Returns:** Generator yielding rows

**Example:**
```php
// Process all sandwiches without loading all into memory
foreach ( Sandwiches::get_all( 100 ) as $sandwich ) {
	// Process each sandwich
	process_sandwich( $sandwich );
}

// With conditions
foreach ( Sandwiches::get_all( 50, 'WHERE is_active = 1', 'created_at DESC' ) as $sandwich ) {
	echo $sandwich['name'] . "\n";
}
```

### `::paginate( array $args, int $per_page = 20, int $page = 1, array $columns = ['*'], ... )`

Advanced paginated query with filtering, sorting, and optional joins.

**Parameters:**
- `$args` - Query arguments (see below)
- `$per_page` - Items per page (max 200)
- `$page` - Current page number
- `$columns` - Columns to select
- `$join_table` - Optional table to join
- `$join_condition` - Join condition
- `$selectable_joined_columns` - Columns from joined table

**Query Arguments:**
```php
$args = [
	'term' => 'search term',  // Search in searchable columns
	'orderby' => 'created_at',  // Column to sort by
	'order' => 'DESC',  // ASC or DESC
	'offset' => 0,  // Starting offset
	'query_operator' => 'AND',  // AND or OR

	// Column filters
	[
		'column' => 'status',
		'value' => 'active',
		'operator' => '=',  // =, !=, >, <, >=, <=, IN, NOT IN
	],
	[
		'column' => 'price_cents',
		'value' => 1000,
		'operator' => '<',
	],
];
```

**Returns:** Array of rows

**Example:**
```php
// Basic pagination
$sandwiches = Sandwiches::paginate( [], 20, 1 );

// With search
$results = Sandwiches::paginate(
	[ 'term' => 'turkey' ],
	20,
	1
);

// Complex filtering
$results = Sandwiches::paginate(
	[
		'orderby' => 'price_cents',
		'order' => 'ASC',
		[
			'column' => 'type',
			'value' => 'classic',
			'operator' => '=',
		],
		[
			'column' => 'price_cents',
			'value' => 1500,
			'operator' => '<',
		],
	],
	20,
	1
);

// With JOIN
$results = Sandwiches::paginate(
	$args,
	20,
	1,
	[ '*' ],  // Columns from main table
	Ingredients::class,  // Join table
	'sandwich_id = id',  // Join condition
	[ 'name', 'quantity' ]  // Columns from joined table
);
```

### `::get_total_items( array $args = [] )`

Count total rows matching the given filters.

**Parameters:**
- `$args` - Same format as `paginate()`

**Returns:** Integer count

**Example:**
```php
$total = Sandwiches::get_total_items();

$active_count = Sandwiches::get_total_items( [
	[
		'column' => 'is_active',
		'value' => true,
		'operator' => '=',
	],
] );
```

## Type Casting

All query methods automatically handle type conversion:

```php
// DateTime objects are converted to MySQL format
Sandwiches::insert( [
	'name' => 'BLT',
	'created_at' => new DateTime(),  // Becomes '2025-09-30 12:00:00'
] );

// Retrieved DateTimes are converted back
$sandwich = Sandwiches::get_by_id( 1 );
$sandwich['created_at'];  // DateTimeInterface object

// Booleans work seamlessly
Sandwiches::insert( [
	'name' => 'Club',
	'is_active' => true,  // Stored as 1
] );

$sandwich = Sandwiches::get_by_id( 1 );
$sandwich['is_active'];  // true (bool)

// JSON columns
Sandwiches::insert( [
	'name' => 'Veggie',
	'metadata' => [ 'calories' => 350, 'vegan' => true ],  // JSON encoded
] );

$sandwich = Sandwiches::get_by_id( 1 );
$sandwich['metadata'];  // array
```

## Complete Example

```php
<?php
namespace Boom\Shakalaka\Tables;

use Boom\Shakalaka\StellarWP\Schema\Tables\Contracts\Table;
use DateTime;

class Orders extends Table {
	const SCHEMA_VERSION = '1.0.0';
	protected static $base_table_name = 'orders';
	protected static $group = 'shop';
	protected static $schema_slug = 'shop-orders';

	public static function get_schema_history(): array {
		// ... schema definition ...
	}
}

// Create an order
$order_id = Orders::insert( [
	'customer_id'  => 123,
	'order_number' => 'ORD-2025-001',
	'status'       => 'pending',
	'total'        => 49.99,
	'created_at'   => new DateTime(),
] );

// Update order status
Orders::update_single( [
	'id'         => $order_id,
	'status'     => 'processing',
	'updated_at' => new DateTime(),
] );

// Get order by ID
$order = Orders::get_by_id( $order_id );

// Get all pending orders
$pending = Orders::get_all_by( 'status', 'pending' );

// Paginate orders with filtering
$recent_orders = Orders::paginate(
	[
		'orderby' => 'created_at',
		'order' => 'DESC',
		[
			'column' => 'status',
			'value' => [ 'pending', 'processing' ],
			'operator' => 'IN',
		],
	],
	25,  // per page
	1    // page number
);

// Search orders
$search_results = Orders::paginate(
	[ 'term' => 'john doe' ],  // Searches searchable columns
	25,
	1
);

// Get total count
$total_pending = Orders::get_total_items( [
	[
		'column' => 'status',
		'value' => 'pending',
		'operator' => '=',
	],
] );

// Bulk update
Orders::update_many( [
	[ 'id' => 1, 'status' => 'shipped' ],
	[ 'id' => 2, 'status' => 'shipped' ],
	[ 'id' => 3, 'status' => 'shipped' ],
] );

// Delete old orders
Orders::delete_many( [ 10, 11, 12 ] );
```

## Custom Transform Method

You can implement a `transform_from_array()` method to convert row data into custom objects:

```php
class Orders extends Table {
	// ...

	public static function transform_from_array( array $data ) {
		// Return a custom object instead of array
		return new Order_Model( $data );
	}
}

// Now query methods return Order_Model objects
$order = Orders::get_by_id( 1 );  // Order_Model instance
$orders = Orders::get_all_by( 'status', 'pending' );  // Array of Order_Model
```

## Hooks and Filters

Query methods provide hooks for customization:

```php
// Before query execution
add_action( 'tec_common_custom_table_query_pre_results', function( $args, $class ) {
	// Modify args, log, etc.
}, 10, 2 );

// After query execution
add_action( 'tec_common_custom_table_query_post_results', function( $results, $args, $class ) {
	// Log, analyze, etc.
}, 10, 3 );

// Filter results
add_filter( 'tec_common_custom_table_query_results', function( $results, $args, $class ) {
	// Modify results
	return $results;
}, 10, 3 );

// Filter WHERE clause
add_filter( 'tec_common_custom_table_query_where', function( $where, $args, $class ) {
	// Add custom WHERE conditions
	return $where;
}, 10, 3 );
```

## Performance Tips

1. **Batch Operations**: Use `insert_many()` and `update_many()` for bulk operations
2. **Pagination**: Use `paginate()` instead of loading all rows
3. **Generators**: Use `get_all()` for memory-efficient processing of large datasets
4. **Indexes**: Ensure columns used in filters have appropriate indexes
5. **Select Specific Columns**: Pass specific columns to `paginate()` instead of `*`

## See Also

- [Column System](columns.md)
- [Index System](indexes.md)
- [Table Schemas](schemas-table.md)
- [Migrating from v2 to v3](migrating-from-v2-to-v3.md)