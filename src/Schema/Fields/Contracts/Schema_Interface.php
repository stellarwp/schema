<?php
/**
 * The API implemented by each custom field. Used in cases where only a portion of an existing table must be modified.
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Fields\Contracts
 */
namespace StellarWP\Schema\Fields\Contracts;

use StellarWP\Schema\Tables\Contracts\Table;

/**
 * Interface Custom_Field_Interface
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Fields\Contracts
 */
interface Schema_Interface {
	/**
	 * Allows extending classes that require it to run some methods
	 * immediately after the table creation or update.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,string> $results A map of results in the format
	 *                                      returned by the `dbDelta` function.
	 *
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	public function after_update( array $results );

	/**
	 * Drop the custom fields.
	 *
	 * @since 1.0.0
	 *
	 * @return bool `true` if successful operation, `false` to indicate a failure.
	 */
	public function drop();

	/**
	 * Gets the custom slug identifier that should identify this field schema.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_schema_slug();

	/**
	 * Returns the SQL to be injected into CREATE TABLE statement for the fields and indexes being created in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since 1.0.0
	 *
	 * @return string The table creation SQL for the fields and indexes being created, in the format supported
	 *                by the `dbDelta` function.
	 */
	public function get_sql();

	/**
	 * The organizational group this field schema belongs to.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function group_name();

	/**
	 * A reference to the table definition we are modifying with new fields.
	 *
	 * @since 1.0.0
	 *
	 * @return Table|null
	 */
	public function table_schema();
}
