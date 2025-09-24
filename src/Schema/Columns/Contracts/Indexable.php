<?php
/**
 * The interface for the indexable column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

/**
 * Interface Indexable
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
interface Indexable {
	/**
	 * Get whether the column is an index.
	 *
	 * @return bool Whether the column is an index.
	 */
	public function is_index(): bool;

	/**
	 * Set whether the column is an index.
	 *
	 * @param bool $is_index Whether the column is an index.
	 *
	 * @return self
	 */
	public function set_is_index( bool $is_index ): self;
}
