<?php
/**
 * The API implemented by each custom table.
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts
 */

namespace StellarWP\Schema\Tables\Contracts;

/**
 * Interface Schema_Interface
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts
 */
interface Schema_Interface {
	/**
	 * Returns the custom table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The base custom table name.
	 */
	public static function base_table_name();

	/**
	 * Drop the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop();

	/**
	 * Empties the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function empty_table();

	/**
	 * Returns whether a table exists or not in the database.
	 *
	 * @since 1.0.0
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
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	public function update();
}
