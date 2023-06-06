# Example field schema class

Here is an example field schema:

```php
<?php
namespace Boom\Shakalaka\Fields;

use Boom\Shakalaka\StellarWP\Schema\Fields\Contracts\Field;

class SandwichesPro extends Field {
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
	protected static $schema_slug = 'food-sandwiches-pro';

	/**
	 * {@inheritdoc}
	 */
	protected $fields = [
		'bread',
	];

	/**
	 * {@inheritdoc}
	 */
	protected function get_definition() {
		return "
				`bread` varchar(50) NOT NULL,
				KEY (`bread`)
		";
	}
}
```

## Anatomy of a field schema class

### `class SandwichesPro extends Fields\Contracts\Field`

It is highly recommended that all field schema classes extend the `PREFIX\StellarWP\Schema\Fields\Contracts\Field` class (obviously, with the Strauss-enabled prefixing in place), but if you really want to change things up, you can opt to implement `PREFIX\StellarWP\Schema\Fields\Contracts\Schema_Interface` instead.

### `const SCHEMA_VERSION`

This constant holds the version number of the field schema. As you change the fields within `::get_definition()`, you will need to be sure to update this constant so that table updates occur when they should.

### `protected static $base_table_name`

This is the base name of the table (without `$wpdb->prefix` applied) that this field schema is associated with. With the above example in mind, the value can be accessed using `PREFIX\Fields\SandwichesPro::base_table_name()`.

### `protected static $group`

This value allows you to group field schemas together so you can do interesting things with them programmatically. There's no explicit use for groups out of the box other than providing some tooling for finding field schemas within a group.

### `protected static $schema_slug`

This is the slug of this field schema and is used as the index for the `PREFIX\StellarWP\Schema::fields()` collection as well as a prefix to the version number for this field schema.

### `protected function get_definition()`

This is the base definition of the fields and indices that the fields schema will be injecting. In isolation, this is not valid SQL, but it should be valid SQL within a `CREATE TABLE` statement.
