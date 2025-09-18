<?php
/**
 * The interface for the auto incrementable column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

/**
 * Interface Auto_Incrementable
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */
interface Auto_Incrementable {
	/**
	 * Get the auto increment of the column.
	 *
	 * @return bool Whether the column is auto increment.
	 */
	public function get_auto_increment(): bool;

	/**
	 * Set the auto increment of the column.
	 *
	 * @param bool $auto_increment Whether the column is auto increment.
	 *
	 * @return self
	 */
	public function set_auto_increment( bool $auto_increment ): self;
}
