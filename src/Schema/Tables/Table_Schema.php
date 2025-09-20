<?php
/**
 * The table schema.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Tables\Contracts;
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Tables;

use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Collections\Index_Collection;
use StellarWP\Schema\Indexes\Primary_Key;
use StellarWP\Schema\Tables\Contracts\Table_Schema_Interface;
use StellarWP\Schema\Indexes\Contracts\Index;
use RuntimeException;

/**
 * Class Table_Schema
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Tables\Contracts;
 */
class Table_Schema implements Table_Schema_Interface {
	/**
	 * The name of the table.
	 *
	 * @var string
	 */
	private string $table_name;

	/**
	 * The columns of the table.
	 *
	 * @var Column_Collection
	 */
	private Column_Collection $columns;

	/**
	 * The indexes of the table.
	 *
	 * @var ?Index_Collection
	 */
	private ?Index_Collection $indexes = null;

	/**
	 * The primary key of the table.
	 *
	 * @var ?Primary_Key
	 */
	private ?Primary_Key $primary_key = null;

	public function __construct( string $table_name, Column_Collection $columns, ?Index_Collection $indexes = null ) {
		$this->table_name = $table_name;
		$this->columns    = $columns;
		$this->indexes    = $indexes ?$indexes->map(
			function( Index $index ): Index {
				$index->set_table_name( $this->get_table_name() );
				return $index;
			}
		) : null;

		$this->validate_columns();
		$this->validate_indexes();
	}

	/**
	 * Gets the name of the table.
	 *
	 * @return string The name of the table.
	 */
	public function get_table_name(): string {
		return $this->table_name;
	}

	/**
	 * Gets the columns of the table.
	 *
	 * @return Column_Collection The columns of the table.
	 */
	public function get_columns(): Column_Collection {
		return $this->columns;
	}

	/**
	 * Gets the indexes of the table.
	 *
	 * @return Index_Collection The indexes of the table.
	 */
	public function get_indexes(): ?Index_Collection {
		return $this->indexes;
	}

	public function get_primary_key(): ?Primary_Key {
		return $this->primary_key;
	}

	/**
	 * Validates the columns of the table.
	 *
	 * @return void
	 */
	protected function validate_columns(): void {
		$columns = $this->get_columns();

		$column_names = [];

		foreach ( $columns as $column ) {
			if ( isset( $column_names[ $column->get_name() ] ) ) {
				throw new RuntimeException( 'Column already exists.' );
			}

			$column_names[ $column->get_name() ] = $column->get_name();
		}
	}

	/**
	 * Validates the indexes of the table.
	 *
	 * @return void
	 */
	protected function validate_indexes(): void {
		$index_columns = $this->get_columns()->get_indexes();

		$indexes         = [];
		$indexed_columns = [];

		foreach ( $index_columns as $index_column ) {
			if ( $index_column->is_primary_key() ) {
				if ( null !== $this->primary_key ) {
					throw new RuntimeException( 'Primary key already set. Only one primary key per table is allowed.' );
				}

				$this->primary_key = new Primary_Key( $index_column->get_name() );
				$indexes[ $index_column->get_name() ] = Index::TYPE_PRIMARY;
				continue;
			}

			$indexes[ $index_column->get_name() ] = $index_column->is_unique() ? Index::TYPE_UNIQUE : Index::TYPE_INDEX;
			$indexed_columns[ $index_column->get_name() ] = $index_column->get_name();
		}

		$all_indexes = $this->get_indexes();
		if ( $all_indexes ) {
			foreach ( $all_indexes as $index ) {
				if ( isset( $indexes[ $index->get_name() ] ) ) {
					throw new RuntimeException( 'Index already exists.' );
				}

				if ( Index::TYPE_PRIMARY === $index->get_type() ) {
					if ( null !== $this->primary_key ) {
						throw new RuntimeException( 'Primary key already set. Only one primary key per table is allowed.' );
					}

					$this->primary_key = new Primary_Key( $index->get_name() );
				}

				$indexes[ $index->get_name() ] = $index->get_type();
				$indexed_columns[ $index->get_name() ] = $index->get_columns();
			}
		}

		if ( array_values( $indexed_columns ) !== array_unique( array_values( $indexed_columns ), SORT_REGULAR ) ) {
			throw new RuntimeException( 'Multiple indexes with the same column combinations.' );
		}
	}
}
