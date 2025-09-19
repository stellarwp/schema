<?php
/**
 * Abstract for Custom Tables.
 *
 * @since TDB
 *
 * @package TEC\Common\Abstracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Tables\Contracts;

use DateTimeInterface;
use StellarWP\Schema\Tables\Contracts\Table as Base_Table;
use StellarWP\Schema\V3\Traits\Custom_Table_Query_Methods;
use StellarWP\Schema\V3\Collections\Column_Collection;

/**
 * Class Custom_Table_Abstract
 *
 * @since TBD
 *
 * @package TEC\Common\Abstracts
 */
abstract class Table extends Base_Table implements Table_Interface {
	use Custom_Table_Query_Methods;

	/**
	 * Returns the table creation SQL in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since 3.0.0
	 *
	 * @return string The table creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	public function get_definition(): string {
		global $wpdb;
		$table_name      = static::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		$columns = static::get_columns();

		$columns_definitions = [];
		foreach ( $columns as $column ) {
			$columns_definitions[] = $column->get_definition();
		}

		$columns_sql = implode( ',' . PHP_EOL, $columns_definitions );

		return "
			CREATE TABLE `{$table_name}` (
				{$columns_sql}
			) {$charset_collate};
		";
	}

	/**
	 * Add indexes after table creation.
	 *
	 * @since 3.0.0
	 *
	 * @param array<string,string> $results A map of results in the format
	 *                                      returned by the `dbDelta` function.
	 *
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	protected function after_update( array $results ) {
		foreach ( static::get_columns() as $column => $definition ) {
			if ( empty( $definition['index'] ) ) {
				continue;
			}

			$this->check_and_add_index( $column );
		}

		return $results;
	}

	/**
	 * An array of all the columns in the table.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	abstract public static function get_columns(): Column_Collection;

	/**
	 * An array of all the columns that are searchable.
	 *
	 * @since 3.0.0
	 *
	 * @return string[]
	 */
	public static function get_searchable_columns(): Column_Collection {
		return [];
	}

	/**
	 * Helper method to check and add an index to a table.
	 *
	 * @since 3.0.0
	 *
	 * @param string $index_name The name of the index.
	 * @param string $columns    The columns to index.
	 *
	 * @return void
	 */
	protected function check_and_add_index( string $index_name, string $columns = '' ): void {
		$index_name = esc_sql( $index_name );

		if ( $this->has_index( $index_name ) ) {
			return;
		}

		$columns = $columns ? esc_sql( $columns ) : $index_name;

		$this->db::query(
			$this->db::prepare( "ALTER TABLE %i ADD INDEX `{$index_name}` ( {$columns} )", esc_sql( static::table_name( true ) ) )
		);

		return;
	}
}
