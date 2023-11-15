<?php

namespace StellarWP\Schema\Fields;

use StellarWP\Schema\Fields\Contracts\Schema_Interface;

class Collection implements \ArrayAccess, \Countable, \Iterator {
	/**
	 * Table groups.
	 *
	 * @var array<string>
	 */
	private $groups = [];

	/**
	 * Collection of fields.
	 *
	 * @var array<mixed>
	 */
	private $fields = [];

	/**
	 * Adds a table to the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param Schema_Interface $field Field instance.
	 *
	 * @return mixed
	 */
	public function add( Schema_Interface $field ) {
		$this->offsetSet( $field::get_schema_slug(), $field );

		$this->register_group( $field );

		return $this->offsetGet( $field::get_schema_slug() );
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return count( $this->fields );
	}

	/**
	 * @inheritDoc
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		return current( $this->fields );
	}

	/**
	 * Alias method for offsetGet.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Field slug.
	 *
	 * @return Schema_Interface
	 */
	public function get( string $key ): Schema_Interface {
		return $this->offsetGet( $key );
	}

	/**
	 * Gets fields by table.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string>|string $tables Tables to filter fields by.
	 * @param \Iterator $iterator Optional. Iterator to filter.
	 *
	 * @return Filters\Table_FilterIterator
	 */
	public function get_by_table( $tables, $iterator = null ): Filters\Table_FilterIterator {
		return new Filters\Table_FilterIterator( $tables, $iterator ?: $this );
	}

	/**
	 * @inheritDoc
	 */
	public function key(): string {
		return key( $this->fields );
	}

	/**
	 * @inheritDoc
	 */
	public function next(): void {
		next( $this->fields );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->fields[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->fields[ $offset ];
	}

	/**
	 * @inheritDoc
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ): void {
		$this->fields[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ): void {
		unset( $this->fields[ $offset ] );
	}

	/**
	 * Registers a group in the group array for the given table.
	 *
	 * @param Schema_Interface $field Field instance.
	 */
	private function register_group( $field ) {
		$group = $field->group_name();

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
		reset( $this->fields );
	}

	/**
	 * Sets a table in the collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Field name.
	 * @param Schema_Interface $field Field instance.
	 *
	 * @return mixed
	 */
	public function set( $name, Schema_Interface $field ) {
		$this->offsetSet( $name, $field );

		$this->register_group( $field );

		return $this->offsetGet( $name );
	}

	/**
	 * @inheritDoc
	 */
	public function valid(): bool {
		return key( $this->fields ) !== null;
	}
}
