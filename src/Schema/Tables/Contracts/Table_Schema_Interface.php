<?php
/**
 * The interface for the table schema.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Tables\Contracts;

use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Collections\Index_Collection;
use StellarWP\Schema\Indexes\Primary_Key;

/**
 * Interface Table_Schema_Interface
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts
 */
interface Table_Schema_Interface {
	/**
	 * Gets the name of the table.
	 *
	 * @return string The name of the table.
	 */
	public function get_table_name(): string;

	/**
	 * Gets the columns of the table.
	 *
	 * @return Column_Collection The columns of the table.
	 */
	public function get_columns(): Column_Collection;

	/**
	 * Gets the indexes of the table.
	 *
	 * @return ?Index_Collection The indexes of the table.
	 */
	public function get_indexes(): ?Index_Collection;

	/**
	 * Gets the primary key of the table.
	 *
	 * @return ?Primary_Key The primary key of the table.
	 */
	public function get_primary_key(): ?Primary_Key;
}
