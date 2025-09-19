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

use StellarWP\Schema\Tables\Contracts\Table as Base_Table;
use StellarWP\Schema\V3\Traits\Custom_Table_Query_Methods;
use StellarWP\Schema\V3\Collections\Column_Collection;
use StellarWP\Schema\V3\Columns\Contracts\Column;
use StellarWP\Schema\V3\Indexes\Contracts\Index;
use StellarWP\Schema\V3\Indexes\Contracts\Primary_Key;
use StellarWP\Schema\V3\Indexes\Contracts\Unique_Key;
use StellarWP\Schema\V3\Indexes\Contracts\Classic_Index;
use EmptyIterator;
use Exception;
use RuntimeException;

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
	 * {@inheritdoc}
	 */
	public static function uid_column(): string {
		$primary_columns = static::primary_columns();
		return array_values( $primary_columns )[0];
	}

	/**
	 * Gets the primary columns for the table.
	 *
	 * @since TBD
	 *
	 * @return array<string> The primary columns for the table.
	 */
	public static function primary_columns(): array {
		return static::get_current_schema()->get_primary_key()->get_columns();
	}

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
		foreach ( static::get_columns()->get_indexes() as $column ) {
			if ( $column->is_primary_key() ) {
				$index = new Primary_Key( $column->get_name() );
			} elseif ( $column->is_unique() ) {
				$index = new Unique_Key( $column->get_name() );
			} else {
				$index = new Classic_Index( $column->get_name() );
			}

			$index->set_table_name( static::table_name( true ) );
			$index->set_columns( $column->get_name() );

			$this->check_and_add_index( $index );
		}

		foreach ( static::get_current_schema()->get_indexes() as $index ) {
			$this->check_and_add_index( $index );
		}

		return $results;
	}

	/**
	 * An array of all the columns in the table.
	 *
	 * @since 3.0.0
	 *
	 * @var Column_Collection
	 */
	public static function get_columns(): Column_Collection {
		return static::get_current_schema()->get_columns();
	}

	/**
	 * An array of all the columns that are searchable.
	 *
	 * @since 3.0.0
	 *
	 * @return string[]
	 */
	public static function get_searchable_columns(): Column_Collection {
		return static::get_columns()->filter( fn ( Column $column ) => $column->is_searchable() );
	}

	/**
	 * Helper method to check and add an index to a table.
	 *
	 * @since 3.0.0
	 *
	 * @param Index $index The index.
	 *
	 * @return void
	 */
	protected function check_and_add_index( Index $index ): void {
		$index_name = esc_sql( $index->get_name() );

		if ( $this->has_index( $index_name ) ) {
			return;
		}

		$this->db::query( $index->get_alter_table_with_index_definition() );
	}


	/**
	 * Gets the defined fields schemas for the table.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $force Force a refresh of the field collection.
	 *
	 * @return EmptyIterator
	 */
	public function get_field_schemas( bool $force = false ) {
		return new EmptyIterator();
	}

	/**
	 * Gets the current schema for the table.
	 *
	 * @since 3.0.0
	 *
	 * @return Table_Schema_Interface The current schema for the table.
	 *
	 * @throws RuntimeException If the current schema version is not found in the schema history.
	 */
	public static function get_current_schema(): Table_Schema_Interface {
		$history = static::get_schema_history();

		$columns = new Column_Collection();
		$columns[] = new ID();
		$columns[] = ( new Integer_Column( 'post_id') )->set_signed( false )->set_is_index( true );

		if ( empty( $history[ static::SCHEMA_VERSION ] ) ) {
			throw new RuntimeException( 'The current schema version is not found in the schema history.' );
		}

		return $history[ static::SCHEMA_VERSION ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		// @phpstan-ignore-next-line
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = $this->get_sql();

		try {
			/**
			 * Hookable action before the table schema has updated.
			 *
			 * @since 3.0.0
			 *
			 * @param string $table_name The prefix-less table name.
			 * @param Table $table The table object.
			 */
			do_action( 'stellarwp_schema_table_before_update_' . static::get_schema_slug(), static::table_name(), $this );

			/**
			 * Hookable action before the table schema has updated.
			 *
			 * @since 3.0.0
			 *
			 * @param string $table_name The prefix-less table name.
			 * @param Table $table The table object.
			 */
			do_action( 'stellarwp_schema_table_before_update', static::table_name(), $this );

			$results = (array) $this->db::delta( $sql );
			$this->archive_previous_version();
			$this->sync_stored_version();
			$results = $this->after_update( $results );

			/**
			 * Hookable action after the table schema has updated.
			 *
			 * @since 3.0.0
			 *
			 * @param string $table_name The prefix-less table name.
			 * @param array $results The results of the table and field schema updates.
			 * @param Table $table The table object.
			 */
			do_action( 'stellarwp_schema_table_after_update_' . static::get_schema_slug(), static::table_name(), $results, $this );

			/**
			 * Hookable action after the table schema has updated.
			 *
			 * @since 3.0.0
			 *
			 * @param string $table_name The prefix-less table name.
			 * @param array $results The results of the table and field schema updates.
			 * @param Table $table The table object.
			 */
			do_action( 'stellarwp_schema_table_after_update', static::table_name(), $results, $this );
		} catch ( Exception $e ) {
			if ( ! has_action( 'stellarwp_schema_table_update_error_' . static::get_schema_slug() ) && ! has_action( 'stellarwp_schema_table_update_error' ) ) {
				throw $e;
			}

			/**
			 * Hookable action after the table schema has failed to update.
			 *
			 * @since 3.0.0
			 *
			 * @param Exception $e     The exception.
			 * @param Table     $table The table object.
			 */
			do_action( 'stellarwp_schema_table_update_error_' . static::get_schema_slug(), $e, $this );

			/**
			 * Hookable action after the table schema has failed to update.
			 *
			 * @since 3.0.0
			 *
			 * @param Exception $e The exception.
			 * @param Table $table The table object.
			 */
			do_action( 'stellarwp_schema_table_update_error', $e, $this );
		}

		return $results;
	}
}
