<?php
/**
 * The interface for the primarable column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

/**
 * Interface Primarable
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */
interface Primarable extends Indexable {
	/**
	 * Get whether the column is a primary key.
	 *
	 * @return bool Whether the column is a primary key.
	 */
	public function is_primary_key(): bool;

	/**
	 * Set whether the column is a primary key.
	 *
	 * @param bool $is_primary_key Whether the column is a primary key.
	 *
	 * @return self
	 */
	public function set_is_primary_key( bool $is_primary_key ): self;
}
