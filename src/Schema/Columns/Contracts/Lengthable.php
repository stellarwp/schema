<?php
/**
 * The interface for the lengthable column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

/**
 * Interface Lengthable
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
interface Lengthable {
	/**
	 * Get the length of the column.
	 *
	 * @return int The length of the column.
	 */
	public function get_length(): int;

	/**
	 * Set the length of the column.
	 *
	 * @param int $length The length of the column.
	 *
	 * @return self
	 */
	public function set_length( int $length ): self;
}
