<?php
/**
 * The API implemented by each custom table.
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts
 */

namespace StellarWP\Schema\Tables\Contracts;
use StellarWP\Schema\Collections\Column_Collection;
use ReturnTypeWillChange;

/**
 * Interface Schema_Interface
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts
 */
interface Table_Interface {
	/**
	 * Returns the custom table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The base custom table name.
	 */
	public static function base_table_name();

	/**
	 * Gets the definition of the table.
	 *
	 * @since 3.0.0
	 *
	 * @return string The definition of the table.
	 */
	public function get_definition(): string;

	/**
	 * Gets the columns of the table.
	 *
	 * @since 3.0.0
	 *
	 * @return Column_Collection The columns of the table.
	 */
	public static function get_columns(): Column_Collection;

	/**
	 * Gets the searchable columns of the table.
	 *
	 * @since 3.0.0
	 *
	 * @return Column_Collection The searchable columns of the table.
	 */
	public static function get_searchable_columns(): Column_Collection;

	/**
	 * Gets the schema history for the table.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string, callable> The schema history for the table. The key is the version and the value is a callable that returns a Table_Schema_Interface object.
	 */
	public static function get_schema_history(): array;

	/**
	 * Gets the schema of the table.
	 *
	 * @since 3.0.0
	 *
	 * @return Table_Schema_Interface The schema of the table.
	 */
	public static function get_current_schema(): Table_Schema_Interface;

	/**
	 * Transforms a result array into a model.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string, mixed> $result_array The result array.
	 *
	 * @return mixed The model.
	 */
	#[ReturnTypeWillChange]
	public static function transform_from_array( array $result_array );

	/**
	 * Drop the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop();

	/**
	 * Empties the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function empty_table();

	/**
	 * Returns whether a table exists or not in the database.
	 *
	 * @since 1.0.0
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return bool
	 */
	public function exists();

	/**
	 * The base table name of the schema.
	 *
	 * @since 1.0.0
	 */
	public static function get_schema_slug();

	/**
	 * Returns the table creation SQL with all field schema SQL injected,
	 * in the format supported by the `dbDelta` function.
	 *
	 * @since 1.0.0
	 *
	 * @return string The table & field schema creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	public function get_sql();

	/**
	 * Gets the table schema's version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_version(): string;

	/**
	 * The organizational group this table belongs to.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function group_name();

	/**
	 * References our stored version versus the version defined in the class.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether our latest schema has been applied.
	 */
	public function is_schema_current();

	/**
	 * Returns the custom table name.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $with_prefix Whether to include the table prefix or not.
	 *
	 * @return string The custom table name, prefixed by the current `wpdb` prefix,
	 *                if required.
	 */
	public static function table_name( $with_prefix = true );

	/**
	 * Returns the name of the column that is guaranteed to uniquely identify an
	 * entry across updates.
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the column that is guaranteed to uniquely identify an
	 *                entry across updates.
	 */
	public static function uid_column();

	/**
	 * Creates, or updates, the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	public function update();
}
