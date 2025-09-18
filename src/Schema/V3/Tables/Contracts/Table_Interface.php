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

	public static function get_columns(): ColumnCollection;

	public static function get_searchable_columns(): ColumnCollection;

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
