<?php

namespace StellarWP\Schema\Builder;

abstract class Abstract_Custom_Table implements Table_Schema_Interface {
	/**
	 * @var string The option key used to store the SCHEMA_VERSION.
	 */
	const SCHEMA_VERSION_OPTION = null;

	/**
	 * @var string The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * @var string The base table name.
	 */
	protected static $base_table_name = '';

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
	 * Allows extending classes that require it to run some methods
	 * immediately after the table creation or update.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return bool Whether a table exists in the database or not.
	 */
	public function exists() {
		global $wpdb;

		$table_name = static::table_name( true );

		return count( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) === 1;
	}

	/**
	 * Returns the table creation SQL in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since TBD
	 *
	 * @return string The table creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	abstract protected function get_update_sql();

	/**
	 * {@inheritdoc}
	 */
	public static function group_name() {
		return static::$group;
	}

	/**
	 * Checks if an index already exists on the table.
	 *
	 * @since TBD
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
	 * Gets the properly namespaced schema version option key.
	 *
	 * @since TBD
	 *
	 * @return string The properly namespaced schema version option key.
	 */
	public function get_schema_version_option(): string {
		return 'stellar_schema_version_' . static::SCHEMA_VERSION_OPTION;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_schema_current() {
		if ( ! static::SCHEMA_VERSION || ! $this->get_schema_version_option() ) {
			// @todo Error?
		}
		$version_applied = get_option( $this->get_schema_version_option() );
		$current_version = static::SCHEMA_VERSION;

		return version_compare( $version_applied, $current_version, '==' );
	}

	/**
	 * Update our stored version with what we have defined.
	 */
	protected function sync_stored_version() {
		if ( ! add_option( $this->get_schema_version_option(), static::SCHEMA_VERSION ) ) {
			update_option( $this->get_schema_version_option(), static::SCHEMA_VERSION );
		}
	}

	/**
	 * Returns the table name, with prefix if required.
	 *
	 * @since TBD
	 *
	 * @return string The table name.
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
		$results = (array) dbDelta( $this->get_update_sql() );
		$this->sync_stored_version();
		$results = $this->after_update( $results );

		return $results;
	}
}
