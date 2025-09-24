<?php
/**
 * The interface for the table.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts;
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Tables\Contracts;

use StellarWP\Schema\Config;
use StellarWP\Schema\Traits\Custom_Table_Query_Methods;
use StellarWP\Schema\Collections\Column_Collection;
use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Indexes\Contracts\Index;
use Exception;
use RuntimeException;

/**
 * Class Table
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Tables\Contracts;
 *
 * @method static Generator<array<string, mixed>> get_all( int $batch_size = 50, string $where_clause = '', string $order_by = '' )
 * @method static bool|int insert( array $entry )
 * @method static bool update_single( array $entry )
 * @method static bool upsert( array $entry )
 * @method static bool|int insert_many( array $entries )
 * @method static bool delete( int $uid, string $column = '' )
 * @method static bool|int delete_many( array $ids, string $column = '', string $more_where = '' )
 * @method static int get_total_items( array $args = [] )
 * @method static bool update_many( array $entries )
 * @method static array paginate( array $args, int $per_page = 20, int $page = 1, array $columns = [ '*' ], string $join_table = '', string $join_condition = '', array $selectable_joined_columns = [], string $output = OBJECT )
 * @method static mixed[] get_all_by( string $column, $value, string $operator = '=', int $limit = 50 )
 * @method static ?mixed get_first_by( string $column, $value )
 * @method static ?mixed get_by_id( $id )
 * @method static array operators()
 * @method static mixed cast_value_based_on_type( string $type, $value )
 */
abstract class Table implements Table_Interface {
	use Custom_Table_Query_Methods;

	/**
	 * @var string|null The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * @var string The base table name.
	 */
	protected static $base_table_name = '';

	/**
	 * @var class-string<\StellarWP\DB\DB> The db class.
	 */
	protected $db;

	/**
	 * @var object The dependency injection container.
	 */
	protected $container;

	/**
	 * @var string The organizational group this table belongs to.
	 */
	protected static $group = '';

	/**
	 * @since 1.0.0
	 *
	 * @var string|null The slug used to identify the custom table.
	 */
	protected static $schema_slug;

	/**
	 * Ordered collection of table update methods.
	 *
	 * Keys should be the SCHEMA_VERSION and the values should be the method to call.
	 *
	 * @var array<string>
	 */
	protected $updates = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param class-string<\StellarWP\DB\DB>|null $db StellarWP\DB object.
	 * @param object $container The container to use.
	 */
	public function __construct( $db = null, $container = null ) {
		$this->db        = $db ?: Config::get_db();
		$this->container = $container ?: Config::get_container();
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
		$indexes = static::get_current_schema()->get_indexes();
		if ( ! $indexes ) {
			return $results;
		}

		foreach ( $indexes as $index ) {
			$this->check_and_add_index( $index );
		}

		return $results;
	}

	/**
	 * An array of all the columns in the table.
	 *
	 * @since 3.0.0
	 *
	 * @return Column_Collection The columns of the table.
	 */
	public static function get_columns(): Column_Collection {
		return static::get_current_schema()->get_columns();
	}

	/**
	 * An array of all the columns that are searchable.
	 *
	 * @since 3.0.0
	 *
	 * @return Column_Collection The searchable columns of the table.
	 */
	public static function get_searchable_columns(): Column_Collection {
		/** @var Column_Collection */
		return static::get_columns()->filter( fn ( Column $column ) => $column->is_searchable() );
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
		static $current_schema = null;

		if ( null !== $current_schema ) {
			return $current_schema;
		}

		$history = static::get_schema_history();

		if ( empty( $history[ static::SCHEMA_VERSION ] ) ) {
			throw new RuntimeException( 'The current schema version is not found in the schema history.' );
		}

		$current_schema = $history[ static::SCHEMA_VERSION ]();

		return $current_schema;
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
	 * Archives the current stored version of the schema.
	 */
	public function archive_previous_version() {
		$current_version = $this->get_stored_version();

		// If there's no current version, there's nothing to mark as previous.
		if ( ! $current_version ) {
			return $current_version;
		}

		return update_option( $this->get_schema_previous_version_option(), $current_version );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function base_table_name() {
		return static::$base_table_name;
	}

	/**
	 * Clear our stored version.
	 */
	protected function clear_stored_version() {
		delete_option( $this->get_schema_version_option() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop() {
		/**
		 * Permit the prevention of dropping a specific table.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $do_drop Whether to drop the table.
		 * @param string $base_table_name The base table name of the table schema.
		 */
		$do_drop = apply_filters( 'stellarwp_schema_table_drop_' . static::base_table_name(), true, static::base_table_name() );

		/**
		 * Permit the prevention of dropping the table.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $do_drop Whether to drop the table.
		 * @param string $base_table_name The base table name of the table schema.
		 */
		$do_drop = apply_filters( 'stellarwp_schema_table_drop', $do_drop, static::base_table_name() );

		if ( ! $do_drop ) {
			return false;
		}

		$this->clear_stored_version();
		if ( ! $this->exists() ) {

			return false;
		}

		$base_table_name = static::base_table_name();
		$this_table      = static::table_name( true );

		/**
		 * Runs before the custom table is dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $base_table_name The base table name.
		 * @param string          $table_name      The full table name.
		 * @param Table_Interface $table_schema    The table schema to be dropped.
		 */
		do_action( 'stellarwp_pre_drop_table', $base_table_name, $this_table, $this );

		global $wpdb;

		/**
		 * Disable foreign key checks so we can drop without issues.
		 * @var \stdClass $key_check
		 */
		$key_check = $this->db::get_row( "SHOW VARIABLES LIKE 'foreign_key_checks'" );
		if ( strtolower( $key_check->Value ) === 'on' ) {
			$this->db::query( "SET foreign_key_checks = 'OFF'" );
		}
		$result = $this->db::query( "DROP TABLE `{$this_table}`" );
		// Put setting back to original value.
		$this->db::query( $this->db::prepare( "SET foreign_key_checks = %s", $key_check->Value ) );

		/**
		 * Runs after the custom table has been dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $base_table_name The base table name.
		 * @param string          $table_name      The full table name.
		 * @param Table_Interface $table_schema    The table schema to be dropped.
		 */
		do_action( 'stellarwp_post_drop_table', $base_table_name, $this_table, $this );

		$base_table_name = static::base_table_name();

		if ( isset( $wpdb->$base_table_name ) ) {
			unset( $wpdb->$base_table_name );
		}

		/**
		 * Runs after the custom table has been removed from $wpdb.
		 *
		 * @since 1.0.0
		 *
		 * @param string          $base_table_name The base table name.
		 * @param string          $table_name      The full table name.
		 * @param Table_Interface $table_schema    The table schema to be dropped.
		 */
		do_action( 'stellarwp_post_drop_table_wpdb_update', $base_table_name, $this_table, $this );

		return $result;
	}

	/**
	 * Empties the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function empty_table() {
		if ( ! $this->exists() ) {
			// There is really nothing to empty here.
			return 0;
		}

		$this_table = static::table_name( true );

		$this->db::query( "SET foreign_key_checks = 0" );
		$result = $this->db::query( "DELETE FROM {$this_table}" );
		$this->db::query( "SET foreign_key_checks = 1" );

		return $result;
	}

	/**
	 * Truncates the custom table.
	 *
	 * @since 1.1.8
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function truncate() {
		if ( ! $this->exists() ) {
			// There is really nothing to empty here.
			return 0;
		}

		$this_table = static::table_name( true );

		$this->db::query( "SET foreign_key_checks = 0" );
		$result = $this->db::query( "TRUNCATE {$this_table}" );
		$this->db::query( "SET foreign_key_checks = 1" );

		return $result;
	}

	/**
	 * Returns whether a table exists in the database or not.
	 *
	 * @since 1.0.0
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return bool Whether a table exists in the database or not.
	 */
	public function exists() {
		$table_name = static::table_name( true );

		return count( $this->db::get_col( $this->db::prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_schema_slug() {
		return static::$schema_slug;
	}

	/**
	 * Gets the properly namespaced schema version option key.
	 *
	 * @since 1.0.0
	 *
	 * @return string The properly namespaced schema version option key.
	 */
	public function get_schema_previous_version_option(): string {
		return 'stellar_schema_previous_version_' . static::get_schema_slug();
	}

	/**
	 * Gets the properly namespaced schema version option key.
	 *
	 * @since 1.0.0
	 *
	 * @return string The properly namespaced schema version option key.
	 */
	public function get_schema_version_option(): string {
		return 'stellar_schema_version_' . static::get_schema_slug();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_sql() {
		return $this->get_definition();
	}

	/**
	 * Gets the previous schema version option value.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null The previous version stored in wp_options.
	 */
	public function get_stored_previous_version() {
		return get_option( $this->get_schema_previous_version_option(), null );
	}

	/**
	 * Gets the current schema version option value.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null The current version stored in wp_options.
	 */
	public function get_stored_version() {
		return get_option( $this->get_schema_version_option(), null );
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
		$indexes_definitions = [];
		foreach ( $columns as $column ) {
			[ $column_definition, $index_definition ] = $column->get_definition();
			$columns_definitions[] = $column_definition;
			$indexes_definitions[] = $index_definition;
		}

		$indexes_definitions = array_filter( $indexes_definitions );

		$indexes_sql = ! empty( $indexes_definitions ) ? implode( ',' . PHP_EOL, $indexes_definitions ) : '';
		$columns_sql = implode( ',' . PHP_EOL, $columns_definitions );

		$columns_sql = $indexes_sql ? $columns_sql . ',' . PHP_EOL : $columns_sql;

		return "
			CREATE TABLE `{$table_name}` (
				{$columns_sql}{$indexes_sql}
			) {$charset_collate};
		";
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_version(): string {
		return static::SCHEMA_VERSION ;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function group_name() {
		return static::$group;
	}

	/**
	 * Checks if an index already exists on the table.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $index      The name of the index to check for.
	 * @param string|null $table_name The table name to search the index for, or `null`
	 *                                to use this table name.
	 *
	 * @throws \StellarWP\DB\Database\Exceptions\DatabaseQueryException If the query fails.
	 *
	 * @return bool Whether the table already has an index or not.
	 */
	public function has_index( $index, $table_name = null ) {
		$table_name = $table_name ?: static::table_name( true );

		$index = $index ?: 'PRIMARY';

		$count = $this->db::table( $this->db::raw( 'information_schema.statistics' ) )
			->whereRaw( 'WHERE TABLE_SCHEMA = DATABASE()' )
			->where( 'TABLE_NAME', $table_name )
			->where( 'INDEX_NAME', $index )
			->count();

		return $count >= 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_schema_current() {
		if ( ! static::SCHEMA_VERSION || ! $this->get_schema_version_option() ) {
			// @todo Error?
		}

		$version_applied = $this->get_stored_version() ?: '';
		$current_version = $this->get_version();

		return version_compare( $version_applied, $current_version, '==' );
	}

	/**
	 * Update our stored version with what we have defined.
	 */
	public function sync_stored_version() {
		$current_version = $this->get_version();

		if ( ! add_option( $this->get_schema_version_option(), $current_version ) ) {
			update_option( $this->get_schema_version_option(), $current_version );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function table_name( $with_prefix = true ) {
		$table_name = static::base_table_name();

		if ( $with_prefix ) {
			global $wpdb;
			$table_name = $wpdb->prefix . $table_name;
		}

		return $table_name;
	}

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
	 * @since 3.0.0
	 *
	 * @return array<string> The primary columns for the table.
	 */
	public static function primary_columns(): array {
		return static::get_current_schema()->get_primary_key()->get_columns();
	}

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		// @phpstan-ignore-next-line
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = $this->get_sql();

		$results = [];

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
			 * @param array $results The results of the table schema updates.
			 * @param Table $table The table object.
			 */
			do_action( 'stellarwp_schema_table_after_update_' . static::get_schema_slug(), static::table_name(), $results, $this );

			/**
			 * Hookable action after the table schema has updated.
			 *
			 * @since 3.0.0
			 *
			 * @param string $table_name The prefix-less table name.
			 * @param array $results The results of the table schema updates.
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

	/**
	 * Checks if a foreign key exists on a table.
	 *
	 * @since 1.1.3
	 *
	 * @param string $foreign_key The foreign key to check for.
	 * @param string $table_name The table name to check. Defaults to the current table.
	 *
	 * @return bool Whether the foreign key exists on the table.
	 */
	public function has_foreign_key( string $foreign_key, string $table_name = '' ): bool {
		$table_name = $table_name ?: static::table_name();

		$count = $this->db::table( $this->db::raw( 'information_schema.statistics' ) )
			->whereRaw( 'WHERE TABLE_SCHEMA = DATABASE()' )
			->where( 'TABLE_NAME', $table_name )
			->where( 'INDEX_NAME', $foreign_key )
			->count();

		return $count >= 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function transform_from_array( array $result_array ) {
		return $result_array;
	}
}
