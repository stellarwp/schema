<?php
/**
 * The abstract class for the index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Indexes\Contracts;

use InvalidArgumentException;

/**
 * Class Abstract_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */
abstract class Abstract_Index implements Index {
	/**
	 * The name of the index.
	 *
	 * Name is optional for primary indexes, required for other indexes.
	 *
	 * @var string
	 */
	private string $name = '';

	/**
	 * The columns of the index.
	 *
	 * @var string[]
	 */
	private array $columns = [];

	/**
	 * The table name of the index.
	 *
	 * @var string
	 */
	private string $table_name;

	/**
	 * The type of the index.
	 *
	 * @var string
	 */
	protected string $type;

	/**
	 * Get the name of the index.
	 *
	 * @return string The name of the index.
	 */
	public function get_name(): string {
		return self::TYPE_PRIMARY === $this->get_type() ? '' : $this->name;
	}

	/**
	 * Set the name of the index.
	 *
	 * Name is optional for primary indexes, required for other indexes.
	 *
	 * @param string $name The name of the index.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the columns of the index.
	 *
	 * @return string[] The columns of the index.
	 */
	public function get_columns(): array {
		return empty( $this->columns ) ? [ $this->get_name() ] : $this->columns;
	}

	/**
	 * Set the columns of the index.
	 *
	 * @param string ...$columns The columns of the index.
	 *
	 * @return self
	 */
	public function set_columns( string ...$columns ): self {
		$this->columns = $columns;
		return $this;
	}

	/**
	 * Get the type of the index.
	 *
	 * @return string The type of the index.
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Set the table name of the index.
	 *
	 * @param string $table_name The table name of the index.
	 *
	 * @return self
	 */
	public function set_table_name( string $table_name ): self {
		$this->table_name = $table_name;
		return $this;
	}

	/**
	 * Get the table name of the index.
	 *
	 * @return string The table name of the index.
	 */
	public function get_table_name(): string {
		return $this->table_name;
	}

	/**
	 * Get the definition of the index.
	 *
	 * @return string The definition of the index.
	 */
	public function get_alter_table_with_index_definition(): string {
		$type = $this->get_type();
		switch ( $type ) {
			case self::TYPE_INDEX:
				$key = 'INDEX ' . $this->get_name();
				break;
			case self::TYPE_UNIQUE:
				$key = 'UNIQUE KEY ' . $this->get_name();
				break;
			case self::TYPE_PRIMARY:
				$key = 'PRIMARY KEY';
				break;
			case self::TYPE_FULLTEXT:
				$key = 'FULLTEXT INDEX ' . $this->get_name();
				break;
			default:
				throw new InvalidArgumentException( "Invalid index type: {$type}" );
		}

		$columns = implode( ', ', $this->get_columns() );

		return "ALTER TABLE `{$this->get_table_name()}` ADD {$key} ({$columns})";
	}
}
