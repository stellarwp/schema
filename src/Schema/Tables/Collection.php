<?php

namespace StellarWP\Schema\Tables;

use StellarWP\Schema\Builder\Abstract_Custom_Table as Table;

class Collection implements \ArrayAccess, \Iterator {
	/**
	 * Table groups.
	 *
	 * @var array<string>
	 */
	private $groups = [];

	/**
	 * Collection of tables.
	 *
	 * @var array<mixed>
	 */
	private $tables = [];

	/**
	 * Adds a table to the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param Table $table Table instance.
	 *
	 * @return mixed
	 */
	public function add( Table $table ) {
		$this->offsetSet( $table->base_table_name(), $table );

		$this->register_group( $table );

		return $this->offsetGet( $table->base_table_name() );
	}

	/**
	 * @inheritDoc
	 */
	public function current() {
		return current( $this->tables );
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
	public function get_by_group( $groups, $iterator = null ) {
		return new Filters\Group_FilterIterator( $groups, $iterator ?: $this );
	}

	/**
	 * Gets tables that need updates.
	 *
	 * @since 1.0.0
	 *
	 * @param \Iterator $iterator Optional. Iterator to filter.
	 *
	 * @return Filters\Needs_Update_FilterIterator
	 */
	public function get_tables_needing_updates( $iterator = null ) {
		return new Filters\Needs_Update_FilterIterator( $iterator ?: $this );
	}

	/**
	 * @inheritDoc
	 */
	public function key(): mixed {
		return key( $this->tables );
	}

	/**
	 * @inheritDoc
	 */
	public function next(): void {
		next( $this->tables );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->tables[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return $this->tables[ $offset ];
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ): void {
		$this->tables[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->tables[ $offset ] );
	}

	/**
	 * Registers a group in the group array for the given table.
	 *
	 * @param Table $table Table instance.
	 */
	private function register_group( $table ) {
		$group = $table->group_name();

		if ( ! isset( $this->groups[ $group ] ) ) {
			$this->groups[ $group ] = $group;
		}
	}

	/**
	 * Helper function for removing a table from the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Table name.
	 */
	public function remove( $name ): void {
		$this->offsetUnset( $name );
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void {
		reset( $this->tables );
	}

	/**
	 * Sets a table in the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Table name.
	 * @param Table $table Table instance.
	 *
	 * @return mixed
	 */
	public function set( $name, Table $table ) {
		$this->offsetSet( $name, $table );

		$this->register_group( $table );

		return $this->offsetGet( $name );
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool {
		return key( $this->tables ) !== null;
	}
}
