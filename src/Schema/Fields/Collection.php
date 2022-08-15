<?php

namespace StellarWP\Schema\Fields;

use StellarWP\Schema\Builder\Abstract_Custom_Field as Field;

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
	 * @param Field $field Field instance.
	 *
	 * @return mixed
	 */
	public function add( Field $field ) {
		$this->offsetSet( $field->get_custom_field_slug_id(), $field );

		$this->register_group( $field );

		return $this->offsetGet( $field->get_custom_field_slug_id() );
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
	public function current() {
		return current( $this->fields );
	}

	/**
	 * @inheritDoc
	 */
	public function key(): mixed {
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
	public function offsetGet( $offset ) {
		return $this->fields[ $offset ];
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ): void {
		$this->fields[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->fields[ $offset ] );
	}

	/**
	 * Registers a group in the group array for the given table.
	 *
	 * @param Field $field Field instance.
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
	 * @param Field $field Field instance.
	 *
	 * @return mixed
	 */
	public function set( $name, Field $field ) {
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
