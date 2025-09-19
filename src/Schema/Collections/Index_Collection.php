<?php
/**
 * The interface for the column collection.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Collections
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Collections;

use StellarWP\Schema\Indexes\Contracts\Index;

/**
 * Class Column_Collection
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Collections
 */
class Index_Collection extends Collection {
	/**
	 * Collection of items.
	 *
	 * @since 3.0.0
	 *
	 * @var array<Index>
	 */
	protected array $resources = [];

	/**
	 * Sets a value in the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $offset The offset to set.
	 * @param Index  $value  The value to set.
	 */
	protected function set( string $offset, $value ): void {
		$this->resources[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function current(): Index {
		return current( $this->resources );
	}

	/**
	 * @inheritDoc
	 *
	 * @param TKey $offset The offset to get.
	 *
	 * @return ?Index
	 */
	public function offsetGet( $offset ): ?Index {
		return $this->resources[ $offset ] ?? null;
	}

	/**
	 * @inheritDoc
	 *
	 * @param TKey  $offset The offset to set.
	 * @param Index $value  The value to set.
	 */
	public function offsetSet( $offset, $value ): void {
		if ( ! $offset ) {
			$offset = (string) count( $this->resources );
		}
		$this->set( $offset, $value );
	}

	/**
	 * Gets a resource from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param TKey $key The key to get.
	 *
	 * @return ?Index
	 */
	public function get( string $key ): ?Index {
		return $this->offsetGet( $key );
	}
}
