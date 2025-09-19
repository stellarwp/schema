<?php
/**
 * The trait for the indexable column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Traits
 */

namespace StellarWP\Schema\Traits;

use StellarWP\Schema\Columns\Contracts\Uniquable;
use StellarWP\Schema\Columns\Contracts\Indexable as Indexable_Contract;
use StellarWP\Schema\Columns\Contracts\Primarable;
use InvalidArgumentException;

/**
 * Trait Indexable
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Traits
 */
trait Indexable {
	/**
	 * Whether the column is an index.
	 *
	 * @var bool
	 */
	private bool $is_index = false;

	/**
	 * Whether the column is unique.
	 *
	 * @var bool
	 */
	private bool $is_unique = false;

	/**
	 * Whether the column is a primary key.
	 *
	 * @var bool
	 */
	private bool $is_primary_key = false;

	/**
	 * Get whether the column is an index.
	 *
	 * @return bool Whether the column is an index.
	 */
	public function is_index(): bool {
		if ( ! $this instanceof Indexable_Contract ) {
			return false;
		}

		return $this->is_index || $this->is_unique || $this->is_primary_key;
	}

	/**
	 * Get whether the column is unique.
	 *
	 * @return bool Whether the column is unique.
	 */
	public function is_unique(): bool {
		if ( ! $this instanceof Uniquable ) {
			return false;
		}

		return $this->is_unique;
	}

	/**
	 * Get whether the column is a primary key.
	 *
	 * @return bool Whether the column is a primary key.
	 */
	public function is_primary_key(): bool {
		if ( ! $this instanceof Primarable ) {
			return false;
		}

		return $this->is_primary_key;
	}

	/**
	 * Set whether the column is an index.
	 *
	 * @param bool $is_index Whether the column is an index.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the column is not an index.
	 */
	public function set_is_index( bool $is_index ): self {
		if ( ! $this instanceof Indexable_Contract ) {
			throw new InvalidArgumentException( 'The column is not an index.' );
		}

		$this->is_index = $is_index;
		return $this;
	}

	/**
	 * Set whether the column is unique.
	 *
	 * @param bool $is_unique Whether the column is unique.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the column is not unique.
	 */
	public function set_is_unique( bool $is_unique ): self {
		if ( ! $this instanceof Uniquable ) {
			throw new InvalidArgumentException( 'The column is not unique.' );
		}

		$this->is_unique = $is_unique;
		return $this;
	}

	/**
	 * Set whether the column is a primary key.
	 *
	 * @param bool $is_primary_key Whether the column is a primary key.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the column is not a primary key.
	 */
	public function set_is_primary_key( bool $is_primary_key ): self {
		if ( ! $this instanceof Primarable ) {
			throw new InvalidArgumentException( 'The column is not a primary key.' );
		}

		$this->is_primary_key = $is_primary_key;
		return $this;
	}
}
