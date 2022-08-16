<?php

namespace StellarWP\Schema\Builder;

use StellarWP\Schema\Container;
use StellarWP\Schema\Fields;

abstract class Abstract_Custom_Table implements Table_Schema_Interface {
	/**
	 * @var string|null The option key used to store the SCHEMA_VERSION.
	 */
	const SCHEMA_VERSION_OPTION = null;

	/**
	 * @var string|null The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * @var string The base table name.
	 */
	protected static $base_table_name = '';

	/**
	 * @var Container The dependency injection container.
	 */
	protected $container;

	/**
	 * @var \Iterator The filtered field collection that applies to this table.
	 */
	protected $field_schemas = null;

	/**
	 * @var string The organizational group this table belongs to.
	 */
	protected static $group = '';

	/**
	 * @var string The field that uniquely identifies a row in the table.
	 */
	protected static $uid_column = '';

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
	 * @param Container $container The container to use.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Container::init();
	}

	/**
	 * Allows extending classes that require it to run some methods
	 * immediately after the table creation or update.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,string> $results A map of results in the format
	 *                                      returned by the `dbDelta` function.
	 *
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	protected function after_update( array $results ) {
		// No-op by default.
		return $results;
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

		$this_table = static::table_name( true );

		global $wpdb;
		// Disable foreign key checks so we can drop without issues.
		$key_check = $wpdb->get_row( "SHOW VARIABLES LIKE 'foreign_key_checks'" );
		if ( strtolower( $key_check->Value ) === 'on' ) {
			$wpdb->query( "SET foreign_key_checks = 'OFF'" );
		}
		$result = $wpdb->query( "DROP TABLE `{$this_table}`" );
		// Put setting back to original value.
		$wpdb->query( $wpdb->prepare( "SET foreign_key_checks = %s", $key_check->Value ) );

		return $result;
	}

	/**
	 * Empties the custom table.
	 *
	 * @since 1.0.0
	 *
	 * @return int|false The number of removed rows, or `false` to indicate a failure.
	 */
	public function empty_table() {
		if ( ! $this->exists() ) {
			// There is really nothing to empty here.
			return 0;
		}

		$this_table = static::table_name( true );

		global $wpdb;

		$wpdb->query( "SET foreign_key_checks = 0" );
		$result = $wpdb->query( "TRUNCATE {$this_table}" );
		$wpdb->query( "SET foreign_key_checks = 1" );

		return $result;
	}

	/**
	 * Returns whether a table exists in the database or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether a table exists in the database or not.
	 */
	public function exists() {
		global $wpdb;

		$table_name = static::table_name( true );

		return count( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) === 1;
	}

	/**
	 * Gets the defined fields schemas for the table.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $force Force a refresh of the field collection.
	 *
	 * @return \Iterator
	 */
	public function get_field_schemas( bool $force = false ) {
		if ( $this->field_schemas === null || $force ) {
			$this->field_schemas = $this->container->make( Fields\Collection::class )->get_by_table( static::base_table_name() );
		}

		return $this->field_schemas;
	}

	/**
	 * Gets the properly namespaced schema version option key.
	 *
	 * @since 1.0.0
	 *
	 * @return string The properly namespaced schema version option key.
	 */
	public function get_schema_version_option(): string {
		return 'stellar_schema_version_' . static::SCHEMA_VERSION_OPTION;
	}

	/**
	 * Returns the table creation SQL in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since 1.0.0
	 *
	 * @return string The table creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	abstract protected function get_update_sql();

	/**
	 * Gets the table schema's version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_version(): string {
		$schema_fields = $this->get_field_schemas( true );

		$version = [
			static::SCHEMA_VERSION,
		];

		foreach ( $schema_fields as $field ) {
			$version[] = $field->get_version();
		}

		return implode( '-', $version );
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
	 * @return bool Whether the table already has an index or not.
	 */
	protected function has_index( $index, $table_name = null ) {
		$table_name = $table_name ?: static::table_name( true );
		global $wpdb;

		return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM information_schema.statistics WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = %s",
					$table_name,
					$index
				)
			) >= 1;
	}

	/**
	 * Inject field schema definitions into the CREATE TABLE SQL.
	 *
	 * @since 1.0.0
	 *
	 * @param Field_Schema_Interface $field_schema The field schema to inject.
	 * @param string $sql The CREATE TABLE SQL to inject into.
	 *
	 * @return string
	 */
	protected function inject_field_schema( Field_Schema_Interface $field_schema, $sql ): string {
		$fields = trim( $field_schema->get_sql() );

		// Inject any extra fields into the table's definition.
		// phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
		$find_first_index_regex =
			'/'
			.   '(,'            // 1) Final comma before indexes.
			.       '(\s*)'     // 2) Capture whitespace before indexes.
			.       '(?='       // Followed by the indexes.
			.           '(?:'
			.               'PRIMARY\s+KEY|(?:UNIQUE|FULLTEXT|SPACIAL)\s+(?:KEY|INDEX)|KEY|INDEX'
			.           ')'
			.       ')'
			.       '[^\n]+'   // Followed by indice columns and names.
			.       '(?!'      // Not followed by another index.
			.           '(?:'
			.               'PRIMARY\s+KEY|(?:UNIQUE|FULLTEXT|SPACIAL)\s+(?:KEY|INDEX)|KEY|INDEX'
			.           ')'
			.       ')'
			.   ')'
			.'/im';            // Case insensitive and multi-line.

		if ( preg_match( $find_first_index_regex, $sql ) ) {
			// Inject additional fields before the indexes.
			$sql = preg_replace(
				$find_first_index_regex,
				",$2{$fields}$1", // $2 is the captured whitespace. $1 is the whitespace PLUS the first index after the last field.
				$sql
			);
		} else {
			// Inject additional fields before the closing parenthesis of the CREATE TABLE statement.
			$sql = preg_replace(
				'/(?<!,)((\s+)\).+(?!\)))/im', // Match the last closing parenthesis and everything after it.
				",$2{$fields}$1",              // $2 is the captured whitespace. $1 is the whitespace PLUS the rest of the statement.
				$sql
			);
		}

		return $sql;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_schema_current() {
		if ( ! static::SCHEMA_VERSION || ! $this->get_schema_version_option() ) {
			// @todo Error?
		}

		$version_applied = get_option( $this->get_schema_version_option() );
		$current_version = $this->get_version();

		return version_compare( $version_applied, $current_version, '==' );
	}

	/**
	 * Update our stored version with what we have defined.
	 */
	protected function sync_stored_version() {
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
	public static function uid_column() {
		return static::$uid_column;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = $this->get_update_sql();

		$field_schemas = $this->get_field_schemas();

		foreach ( $field_schemas as $field_schema ) {
			$sql = $this->inject_field_schema( $field_schema, $sql );
		}

		$results = (array) dbDelta( $sql );
		$this->sync_stored_version();
		$results = $this->after_update( $results );

		foreach ( $field_schemas as $field_schema ) {
			$sql = $field_schema->after_update( $results );
		}

		return $results;
	}
}
