<?php
/**
 * The interface for the precisionable column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

/**
 * Interface Precisionable
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */
interface Precisionable {
	/**
	 * Get the precision of the column.
	 *
	 * @return int The precision of the column.
	 */
	public function get_precision(): int;

	/**
	 * Set the precision of the column.
	 *
	 * @param int $precision The precision of the column.
	 *
	 * @return self
	 */
	public function set_precision( int $precision ): self;
}
