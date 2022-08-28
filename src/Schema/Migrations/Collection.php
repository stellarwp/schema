<?php

namespace StellarWP\Schema\Migrations;

use StellarWP\Schema\Migrations\Contracts\Migration;

class Collection implements \ArrayAccess, \Countable, \Iterator {
	/**
	 * Table groups.
	 *
	 * @var array<string>
	 */
	private $groups = [];

	/**
	 * Collection of migrations.
	 *
	 * @var array<mixed>
	 */
	private $migrations = [];

	/**
	 * Adds a migration to the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param Migration $migration Migration instance.
	 *
	 * @return mixed
	 */
	public function add( Migration $migration ) {
		$this->offsetSet( $migration::id(), $migration );

		$this->register_group( $migration );

		return $this->offsetGet( $migration::id() );
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return iterator_count( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function current() {
		return current( $this->migrations );
	}

	/**
	 * Alias method for offsetGet.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Migration id.
	 *
	 * @return Migration
	 */
	public function get( string $key ) : Migration {
		return $this->offsetGet( $key );
	}

	/**
	 * Gets tables by group.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string>|string $groups Groups to filter tables by.
	 * @param \Iterator $iterator Optional. Iterator to filter.
	 *
	 * @return Filters\Group_FilterIterator
	 */
	public function get_by_group( $groups, $iterator = null ): Filters\Group_FilterIterator {
		return new Filters\Group_FilterIterator( $groups, $iterator ?: $this );
	}

	/**
	 * @inheritDoc
	 */
	public function key(): string {
		return key( $this->migrations );
	}

	/**
	 * @inheritDoc
	 */
	public function next(): void {
		next( $this->migrations );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->migrations[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return $this->migrations[ $offset ];
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ): void {
		$this->migrations[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->migrations[ $offset ] );
	}

	/**
	 * Registers a group in the group array for the given migration.
	 *
	 * @param Migration $migration Migration instance.
	 */
	private function register_group( $migration ) {
		$group = $migration->group_name();

		if ( ! isset( $this->groups[ $group ] ) ) {
			$this->groups[ $group ] = $group;
		}
	}

	/**
	 * Helper function for removing a migration from the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Migration name.
	 */
	public function remove( $id ): void {
		$this->offsetUnset( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void {
		reset( $this->migrations );
	}

	/**
	 * Sets a migration in the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Migration id.
	 * @param Migration $migration Migration instance.
	 *
	 * @return mixed
	 */
	public function set( $id, Migration $migration ) {
		$this->offsetSet( $id, $migration );

		$this->register_group( $migration );

		return $this->offsetGet( $id );
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool {
		return key( $this->migrations ) !== null;
	}
}

