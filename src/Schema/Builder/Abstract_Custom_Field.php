<?php
/**
 * Groups the base methods and functions used by all custom field implementations.
 *
 * @since   TBD
 *
 * @package StellarWP\Schema\Builder
 */

namespace StellarWP\Schema\Builder;

/**
 * Class Abstract_Custom_Field
 *
 * @since   TBD
 *
 * @package StellarWP\Schema\Builder
 */
abstract class Abstract_Custom_Field implements Field_Schema_Interface {
	/**
	 * @var string|null The option key used to store the SCHEMA_VERSION.
	 */
	const SCHEMA_VERSION_OPTION = null;

	/**
	 * @var string|null The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * @var string The slug used to identify the custom field alterations.
	 */
	protected static $custom_field_slug_id = '';

	/**
	 * @var string The organizational group this field set belongs to.
	 */
	protected static $group = '';

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

		global $wpdb;
		$this_table   = $this->table_schema()::table_name( true );
		$drop_columns = 'DROP COLUMN `' . implode( '`, DROP COLUMN `', $this->fields() ) . '`';

		return $wpdb->query( sprintf( "ALTER TABLE %s %s", $this_table, $drop_columns ) );
	}

	/**
	 * Returns whether a fields' schema definition exists in the table or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether a set of fields exists in the database or not.
	 */
	public function exists() {
		global $wpdb;
		$table_name = $this->table_schema()::table_name( true );
		$q          = 'select `column_name` from information_schema.columns
					where table_schema = database()
					and `table_name` = %s';
		$rows       = $wpdb->get_results( $wpdb->prepare( $q, $table_name ) );
		$fields     = $this->fields();
		$rows       = array_map( function ( $row ) {
			return $row->column_name;
		}, $rows );

		foreach ( $fields as $field ) {
			if ( ! in_array( $field, $rows, true ) ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * @since TBD
	 *
	 * @return array<string>
	 */
	abstract public function fields();

	/**
	 * The base table name of the schema.
	 */
	public static function get_custom_field_slug_id() {
		return static::$custom_field_slug_id;
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
	 * Returns the table creation SQL for the fields being created in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since TBD
	 *
	 * @return string The table creation SQL for the fields being created, in the format supported
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
	 * @inheritDoc
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
