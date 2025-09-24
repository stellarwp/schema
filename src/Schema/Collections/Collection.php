<?php
/**
 * Abstract collection.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Collections
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Collections;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use ReturnTypeWillChange;

/**
 * Abstract collection.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Collections
 */
abstract class Collection implements ArrayAccess, Iterator, Countable, JsonSerializable {
	/**
	 * Collection of items.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	protected array $resources = [];

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param array $resources An array of items.
	 */
	final public function __construct( array $resources = [] ) {
		foreach ( $resources as $offset => $value ) {
			$this->set( (string) $offset, $value );
		}
	}

	/**
	 * Sets a value in the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $offset The offset to set.
	 * @param mixed  $value  The value to set.
	 */
	protected function set( string $offset, $value ): void {
		$this->resources[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	#[ReturnTypeWillChange]
	public function current() {
		return current( $this->resources );
	}

	/**
	 * @inheritDoc
	 */
	public function key(): ?string {
		return (string) key( $this->resources );
	}

	/**
	 * @inheritDoc
	 */
	public function next(): void {
		next( $this->resources );
	}

	/**
	 * @inheritDoc
	 *
	 * @param string $offset The offset to check.
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ): bool {
		return array_key_exists( $offset, $this->resources );
	}

	/**
	 * @inheritDoc
	 *
	 * @param string $offset The offset to get.
	 *
	 * @return ?mixed
	 */
	#[ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->resources[ $offset ] ?? null;
	}

	/**
	 * @inheritDoc
	 *
	 * @param string $offset The offset to set.
	 * @param mixed  $value  The value to set.
	 */
	public function offsetSet( $offset, $value ): void {
		if ( ! $offset ) {
			$offset = (string) count( $this->resources );
		}
		$this->set( $offset, $value );
	}

	/**
	 * @inheritDoc
	 *
	 * @param string $offset The offset to unset.
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->resources[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void {
		reset( $this->resources );
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool {
		return key( $this->resources ) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return count( $this->resources );
	}

	/**
	 * Returns the collection as an array.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->resources;
	}

	/**
	 * Maps the collection to an array.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback The callback to map the collection to an array.
	 *
	 * @return self
	 */
	public function map( callable $callback ): self {
		return new static( array_map( $callback, $this->resources ) );
	}

	/**
	 * Filters the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param callable $callback The callback to filter the collection.
	 *
	 * @return self
	 */
	public function filter( callable $callback ): self {
		return new static( array_filter( $this->resources, $callback ) );
	}

	/**
	 * Gets a resource from the collection.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key The key to get.
	 *
	 * @return ?mixed
	 */
	#[ReturnTypeWillChange]
	public function get( string $key ) {
		return $this->offsetGet( $key );
	}
}
