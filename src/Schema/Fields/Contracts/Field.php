<?php
/**
 * Groups the base methods and functions used by all custom field implementations.
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Fields\Contracts
 */

namespace StellarWP\Schema\Fields\Contracts;

use StellarWP\Schema\Config;
use StellarWP\Schema\Schema;

/**
 * Class Field
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema\Fields\Contracts
 */
abstract class Field implements Schema_Interface {
	/**
	 * @since 1.0.0
	 *
	 * @var string|null The version number for this schema definition.
	 */
	const SCHEMA_VERSION = null;

	/**
	 * @since 1.0.0
	 *
	 * @var string The base table name.
	 */
	protected static $base_table_name = '';

	/**
	 * @var object The dependency injection container.
	 */
	protected $container;

	/**
	 * @var string The db class.
	 */
	protected $db;

	/**
	 * @since 1.0.0
	 *
	 * @var string The slug used to identify the custom field alterations.
	 */
	protected static $schema_slug = '';

	/**
	 * @since 1.0.0
	 *
	 * @var array<string> Custom fields defined in this field schema.
	 */
	protected $fields = [];

	/**
	 * @var string The organizational group this field set belongs to.
	 */
	protected static $group = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $db StellarWP\DB object.
	 * @param object $container The container to use.
	 */
	public function __construct( $db = null, $container = null ) {
		$this->db        = $db ?: Config::get_db();
		$this->container = $container ?: Config::get_container();
	}

	/**
	 * {@inheritdoc}
	 */
	public function after_update( array $results ) {
		// No-op by default.
		return $results;
	}

	/**
	 * Gets the base table name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function base_table_name() {
		return static::$base_table_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function drop() {
		if ( ! $this->exists() ) {
			return false;
		}

		$schema_slug = static::get_schema_slug();

		/**
		 * Allows for the enabling of field removal.
		 *
		 * Defaults to false.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $can_drop_field Whether or not the field schema can be dropped.
		 * @param string $schema_slug The slug of the field schema.
		 */
		$can_drop_field = apply_filters( "stellarwp_drop_field_enabled_{$schema_slug}", false, $schema_slug );

		/**
		 * Allows for the enabling of field removal.
		 *
		 * Defaults to false.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $can_drop_field Whether or not the field schema can be dropped.
		 * @param string $schema_slug The slug of the field schema.
		 */
		$can_drop_field = apply_filters( "stellarwp_drop_field_enabled", $can_drop_field, $schema_slug );

		if ( ! $can_drop_field ) {
			return false;
		}

		/**
		 * Runs before the custom field is dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param string $schema_slug The schema slug.
		 * @param Schema_Interface $field_schema The field schema to be dropped.
		 */
		do_action( 'stellarwp_pre_drop_field', $schema_slug, $this );

		$this_table   = $this->table_schema()::table_name( true );
		$drop_columns = 'DROP COLUMN `' . implode( '`, DROP COLUMN `', $this->fields() ) . '`';

		$results = $this->db::query( sprintf( "ALTER TABLE %s %s", $this_table, $drop_columns ) );

		/**
		 * Runs after the custom field has been dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param string $schema_slug The schema slug.
		 * @param Schema_Interface $field_schema The field schema to be dropped.
		 */
		do_action( 'stellarwp_post_drop_field', $schema_slug, $this );

		$this->table_schema()->sync_stored_version();

		/**
		 * Runs after the custom field's table schema's version has been synchronized.
		 *
		 * @since 1.0.0
		 *
		 * @param string $schema_slug The schema slug.
		 * @param Schema_Interface $field_schema The field schema to be dropped.
		 */
		do_action( 'stellarwp_post_drop_field_table_version_sync', $schema_slug, $this );

		return $results;
	}

	/**
	 * Returns whether a fields' schema definition exists in the table or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether a set of fields exists in the database or not.
	 */
	public function exists() {
		global $wpdb;
		$table_schema = $this->table_schema();

		if ( $table_schema === null ) {
			return false;
		}

		$table_name = $table_schema::table_name( true );

		$rows = $this->db::table( $this->db::raw( 'information_schema.statistics' ) )
			->select( 'column_name' )
			->whereRaw( 'WHERE TABLE_SCHEMA = DATABASE()' )
			->where( 'TABLE_NAME', $table_name )
			->getAll();

		$fields = $this->fields();
		$rows   = array_map( function ( $row ) {
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
	 * Fields being added to the table.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string>
	 */
	public function fields() {
		return (array) $this->fields;
	}

	/**
	 * The base table name of the schema.
	 *
	 * @since 1.0.0
	 */
	public static function get_schema_slug() {
		return static::$schema_slug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_sql() {
		return $this->get_definition();
	}

	/**
	 * {@inheritdoc}
	 */
	abstract protected function get_definition();

	/**
	 * Gets the field schema's version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_version(): string {
		return static::get_schema_slug() . '-' . static::SCHEMA_VERSION;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function group_name() {
		return static::$group;
	}

	/**
	 * {@inheritdoc}
	 */
	public function table_schema() {
		$tables          = Schema::tables();
		$base_table_name = static::base_table_name();

		if ( ! isset( $tables[ $base_table_name ] ) ) {
			return null;
		}

		return $tables->offsetGet( $base_table_name );
	}
}
