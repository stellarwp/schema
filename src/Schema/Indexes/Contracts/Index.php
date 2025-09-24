<?php
/**
 * The interface for the index.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes\Contracts;

use InvalidArgumentException;

/**
 * Interface Index
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */
interface Index {
	/**
	 * The type of the index.
	 */
	public const TYPE_INDEX = 'INDEX';

	/**
	 * The type of the unique index.
	 */
	public const TYPE_UNIQUE = 'UNIQUE';

	/**
	 * The type of the primary index.
	 */
	public const TYPE_PRIMARY = 'PRIMARY';

	/**
	 * The type of the fulltext index.
	 */
	public const TYPE_FULLTEXT = 'FULLTEXT';

	/**
	 * The allowed types of the index.
	 */
	public const ALLOWED_TYPES = [
		self::TYPE_INDEX,
		self::TYPE_UNIQUE,
		self::TYPE_PRIMARY,
		self::TYPE_FULLTEXT,
	];

	/**
	 * Get the name of the index.
	 *
	 * @return string The name of the index.
	 */
	public function get_name(): string;

	/**
	 * Get the columns of the index.
	 *
	 * @return string[] The columns of the index.
	 */
	public function get_columns(): array;

	/**
	 * Get the type of the index.
	 *
	 * @return string The type of the index.
	 */
	public function get_type(): string;

	/**
	 * Get the table name of the index.
	 *
	 * @return string The table name of the index.
	 */
	public function get_table_name(): string;

	/**
	 * Set the name of the index.
	 *
	 * Name is optional for primary indexes, required for other indexes.
	 *
	 * @param string $name The name of the index.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self;

	/**
	 * Set the columns of the index.
	 *
	 * @param string ...$columns The columns of the index.
	 *
	 * @return self
	 */
	public function set_columns( string ...$columns ): self;

	/**
	 * Set the table name of the index.
	 *
	 * @param string $table_name The table name of the index.
	 *
	 * @return self
	 */
	public function set_table_name( string $table_name ): self;

	/**
	 * Get the definition of the index.
	 *
	 * @return string The definition of the index.
	 */
	public function get_alter_table_with_index_definition(): string;
}
