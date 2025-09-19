<?php
/**
 * The interface for the column collection.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Collections
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Collections;

use StellarWP\Schema\V3\Columns\Contracts\Column;
use StellarWP\Schema\V3\Columns\Contracts\Indexable;

/**
 * Class Column_Collection
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Collections
 */
class Column_Collection extends Collection {
	/**
	 * Collection of items.
	 *
	 * @since 3.0.0
	 *
	 * @var array<Column>
	 */
	protected array $resources = [];

	/**
	 * Sets a value in the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $offset The offset to set.
	 * @param Column $value  The value to set.
	 */
	protected function set( string $offset, $value ): void {
		$this->resources[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function current(): Column {
		return current( $this->resources );
	}

	/**
	 * @inheritDoc
	 *
	 * @param TKey $offset The offset to get.
	 *
	 * @return ?Column
	 */
	public function offsetGet( $offset ): ?Column {
		return $this->resources[ $offset ] ?? null;
	}

	/**
	 * @inheritDoc
	 *
	 * @param TKey   $offset The offset to set.
	 * @param Column $value  The value to set.
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
	 * @return ?Column
	 */
	public function get( string $key ): ?Column {
		return $this->offsetGet( $key );
	}

	/**
	 * Gets the indexes from the collection.
	 *
	 * @since TBD
	 *
	 * @return array<Column>
	 */
	public function get_indexes(): array {
		return array_filter(
			$this->resources,
			function ( Column $column ) {
				return $column instanceof Indexable && $column->is_index();
			}
		);
	}
}
