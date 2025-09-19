<?php
/**
 * The interface for the column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

use InvalidArgumentException;

/**
 * Interface Column_Interface
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */
interface Column_Interface extends PHP_Types, Column_Types {
	/**
	 * Get the type of the column.
	 *
	 * @return string The type of the column.
	 */
	public function get_type(): string;

	/**
	 * Get the PHP type of the column.
	 *
	 * @return string The PHP type of the column.
	 */
	public function get_php_type(): string;

	/**
	 * Get the name of the column.
	 *
	 * @return string The name of the column.
	 */
	public function get_name(): string;

	/**
	 * Get the nullable of the column.
	 *
	 * @return bool Whether the column can be null.
	 */
	public function get_nullable(): bool;

	/**
	 * Get the default of the column.
	 *
	 * @return mixed The default value of the column.
	 */
	public function get_default(): mixed;

	/**
	 * Get the on update value of the column.
	 *
	 * @return ?string The on update value of the column.
	 */
	public function get_on_update(): ?string;

	/**
	 * Get the definition of the column.
	 *
	 * @return string The definition of the column.
	 */
	public function get_definition(): string;

	/**
	 * Get whether the column is searchable.
	 *
	 * @return bool Whether the column is searchable.
	 */
	public function is_searchable(): bool;

	/**
	 * Set the type of the column.
	 *
	 * @param string $type The type of the column.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the type is not valid.
	 */
	public function set_type( string $type ): self;

	/**
	 * Set the PHP type of the column.
	 *
	 * @param string $php_type The PHP type of the column.
	 *
	 * @return self
	 */
	public function set_php_type( string $php_type ): self;

	/**
	 * Set the name of the column.
	 *
	 * @param string $name The name of the column.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self;

	/**
	 * Set the default of the column.
	 *
	 * @param mixed $default The default value of the column.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the default is not valid.
	 */
	public function set_default( mixed $default ): self;

	/**
	 * Set the nullable of the column.
	 *
	 * @param bool $nullable Whether the column can be null.
	 *
	 * @return self
	 */
	public function set_nullable( bool $nullable ): self;

	/**
	 * Set the searchable of the column.
	 *
	 * @param bool $searchable Whether the column is searchable.
	 *
	 * @return self
	 */
	public function set_searchable( bool $searchable ): self;

	/**
	 * Set the on update value of the column.
	 *
	 * @param ?string $on_update The on update value of the column.
	 *
	 * @return self
	 */
	public function set_on_update( ?string $on_update ): self;
}
