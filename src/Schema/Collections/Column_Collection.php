<?php
/**
 * The interface for the column collection.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Collections
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Collections;

use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Columns\Contracts\Indexable;

/**
 * Class Column_Collection
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Collections
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
	 * @param string $offset The offset to get.
	 *
	 * @return ?Column
	 */
	public function offsetGet( $offset ): ?Column {
		return $this->resources[ $offset ] ?? null;
	}

	/**
	 * @inheritDoc
	 *
	 * @param string $offset The offset to set.
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
	 * @param string $key The key to get.
	 *
	 * @return ?Column
	 */
	public function get( string $key ): ?Column {
		foreach ( $this->resources as $column ) {
			if ( $column->get_name() === $key ) {
				return $column;
			}
		}

		return null;
	}

	/**
	 * Gets the names from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @return array<string>
	 */
	public function get_names(): array {
		return array_map(
			function ( Column $column ) {
				return $column->get_name();
			},
			$this->resources
		);
	}

	/**
	 * Gets the indexes from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @return array<Column>
	 */
	public function get_indexes(): array {
		return array_filter( $this->resources, fn ( Column $column ) => $column->is_index() );
	}
}
