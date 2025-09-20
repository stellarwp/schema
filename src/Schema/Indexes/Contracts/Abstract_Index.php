<?php
/**
 * The abstract class for the index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes\Contracts;

use InvalidArgumentException;

/**
 * Class Abstract_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
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
	 * Constructor.
	 *
	 * @param string $name The name of the index.
	 */
	public function __construct( string $name = '' ) {
		$this->name = $name;
	}

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
		$name = esc_sql( $this->get_name() );
		switch ( $type ) {
			case self::TYPE_INDEX:
				$key = 'INDEX ' . $name;
				break;
			case self::TYPE_UNIQUE:
				$key = 'UNIQUE KEY ' . $name;
				break;
			case self::TYPE_PRIMARY:
				$key = 'PRIMARY KEY';
				break;
			case self::TYPE_FULLTEXT:
				$key = 'FULLTEXT INDEX ' . $name;
				break;
			default:
				throw new InvalidArgumentException( "Invalid index type: {$type}" );
		}

		$columns = implode( ', ', array_map( 'esc_sql', $this->get_columns() ) );

		$table_name = esc_sql( $this->get_table_name() );

		return "ALTER TABLE `{$table_name}` ADD {$key} ({$columns})";
	}
}
