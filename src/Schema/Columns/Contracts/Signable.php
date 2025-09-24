<?php
/**
 * The interface for the signable column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

/**
 * Interface Signable
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
interface Signable {
	/**
	 * Get the signed of the column.
	 *
	 * @return bool Whether the column is signed.
	 */
	public function get_signed(): bool;

	/**
	 * Set the signed of the column.
	 *
	 * @param bool $signed Whether the column is signed.
	 *
	 * @return self
	 */
	public function set_signed( bool $signed ): self;
}
