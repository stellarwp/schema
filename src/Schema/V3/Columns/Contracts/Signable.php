<?php
/**
 * The interface for the signable column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

/**
 * Interface Signable
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
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
	 * @param int $signed Whether the column is signed.
	 *
	 * @return self
	 */
	public function set_signed( bool $signed ): self;
}
