<?php
/**
 * The interface for the uniquable column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

/**
 * Interface Uniquable
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
interface Uniquable extends Indexable {
	/**
	 * Get whether the column is unique.
	 *
	 * @return bool Whether the column is unique.
	 */
	public function is_unique(): bool;

	/**
	 * Set whether the column is unique.
	 *
	 * @param bool $is_unique Whether the column is unique.
	 *
	 * @return self
	 */
	public function set_is_unique( bool $is_unique ): self;
}
