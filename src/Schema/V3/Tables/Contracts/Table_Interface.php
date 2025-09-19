<?php
/**
 * The interface for the custom table.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Tables\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Tables\Contracts;

use StellarWP\Schema\Tables\Contracts\Schema_Interface as Base_Interface;
use StellarWP\Schema\V3\Collections\Column_Collection;
use ReturnTypeWillChange;

/**
 * Interface Table_Interface
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\V3\Tables\Contracts
 */
interface Table_Interface extends Base_Interface {
	/**
	 * Gets the definition of the table.
	 *
	 * @return string The definition of the table.
	 */
	public function get_definition(): string;

	/**
	 * Gets the columns of the table.
	 *
	 * @return Column_Collection The columns of the table.
	 */
	public static function get_columns(): Column_Collection;

	/**
	 * Gets the searchable columns of the table.
	 *
	 * @return Column_Collection The searchable columns of the table.
	 */
	public static function get_searchable_columns(): Column_Collection;

	/**
	 * Gets the schema of the table.
	 *
	 * @return Schema_Interface The schema of the table.
	 */
	public static function get_schema(): Table_Schema_Interface;

	/**
	 * Transforms a result array into a model.
	 *
	 * @param array<string, mixed> $result_array The result array.
	 *
	 * @return mixed The model.
	 */
	#[ReturnTypeWillChange]
	public static function transform_from_array( array $result_array );
}
