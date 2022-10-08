<?php

namespace StellarWP\Schema;

use StellarWP\Schema\Config;
use StellarWP\Schema\Fields;
use StellarWP\Schema\Fields\Contracts\Schema_Interface as Field_Schema_Interface;
use StellarWP\Schema\Tables;
use StellarWP\Schema\Tables\Contracts\Schema_Interface as Table_Schema_Interface;
use StellarWP\Schema\Tables\Filters\Group_FilterIterator;
use WP_CLI;

class Builder {
	/**
	 * Container.
	 *
	 * @var object
	 */
	protected $container;

	/**
	 * StellarWP\DB class.
	 *
	 * @var string
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param string|null $db StellarWP\DB class.
	 * @param object $container Container instance.
	 */
	public function __construct( $db = null, $container = null ) {
		$this->db        = $db ?: Config::get_db();
		$this->container = $container ?: Config::get_container();
	}

	/**
	 * Whether all the custom tables exist or not. Does not check custom fields.
	 *
	 * Note: the method will return `false` if even one table is missing.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $group An optional group name to restrict the check to.
	 *
	 * @return bool Whether all custom tables exist or not. Does not check custom fields.
	 */
	public function all_tables_exist( $group = null ) {
		$table_schemas = $this->get_registered_table_schemas();

		if ( null !== $group ) {
			$table_schemas = new Group_FilterIterator( (array) $group, $table_schemas );
		}

		if ( count( $table_schemas ) === 0 ) {
			// No table class was even found, so yeah, all tables exist.
			return true;
		}

		$result = $this->db::get_col( 'SHOW TABLES' );
		foreach ( $table_schemas as $table_schema ) {
			if ( ! in_array( $table_schema::table_name(), $result, true ) ) {

				return false;
			}
		}

		return true;
	}

	/**
	 * Trigger actions to drop the custom tables.
	 *
	 * @since 1.0.0
	 */
	public function down() {
		/**
		 * Runs before the custom tables are dropped.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stellarwp_pre_drop_tables' );

		$table_schemas = $this->get_registered_table_schemas();

		/**
		 * Filters the tables to be dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param \Iterator $table_schemas A list of Table_Schema_Interface objects that will have their tables dropped.
		 */
		$table_schemas = apply_filters( 'stellarwp_tables_to_drop', $table_schemas );

		foreach ( $table_schemas as $table_schema ) {
			$table_schema->drop();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stellarwp_post_drop_tables' );

		/**
		 * Runs before the custom fields are dropped.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stellarwp_pre_drop_fields' );

		$field_schemas = $this->get_registered_field_schemas();

		/**
		 * Filters the fields to be dropped.
		 *
		 * @since 1.0.0
		 *
		 * @param \Iterator $field_classes A list of Field_Schema_Interface objects that will have their fields dropped.
		 */
		$field_schemas = apply_filters( 'stellarwp_fields_to_drop', $field_schemas );

		foreach ( $field_schemas as $field_schema ) {
			$field_schema->drop();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since 1.0.0
		 */
		do_action( 'stellarwp_post_drop_fields' );
	}

	/**
	 * Empties the plugin custom tables.
	 *
	 * @since 1.0.0
	 */
	public function empty_custom_tables() {
		$schemas = $this->get_registered_table_schemas();
		foreach ( $schemas as $custom_table ) {
			/** @var Table_Schema_Interface $custom_table */
			WP_CLI::debug( 'Emptying table ' . $custom_table::table_name(), 'StellarWP' );
			$custom_table->empty_table();
		}
	}

	/**
	 * Get the registered field handlers.
	 *
	 * @since 1.0.0
	 *
	 * @return Fields\Collection
	 */
	public function get_registered_field_schemas(): Fields\Collection {
		return $this->container->get( Fields\Collection::class );
	}

	/**
	 * Get the md5 hash of all the registered schemas classes with their versions.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_registered_schemas_version_hash() :string {
		$schemas = $this->get_registered_table_schemas();

		$versions = [];
		foreach( $schemas as $schema ) {
			// Skip if not an Interface of Table.
			if ( ! $schema instanceof Table_Schema_Interface ) {
				continue;
			}

			$versions[ $schema::base_table_name() ] = $schema->get_version();
		}

		// Sort to avoid hash changing due to order changes.
		ksort( $versions );

		return md5( json_encode( $versions ) );
	}

	/**
	 * Get the registered table handlers.
	 *
	 * @since 1.0.0
	 *
	 * @return Tables\Collection
	 */
	public function get_registered_table_schemas(): Tables\Collection {
		return $this->container->get( Tables\Collection::class );
	}

	/**
	 * Get the registered table handlers that need updates.
	 *
	 * @since 1.0.0
	 *
	 * @return Tables\Collection
	 */
	public function get_table_schemas_that_need_updates() {
		return $this->container->get( Tables\Collection::class )->get_tables_needing_updates();
	}

	/**
	 * Filters the list of tables for a blog adding the ones created by the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tables An array of table names for the blog.
	 *
	 * @return array<string> A filtered array of table names, including prefix.
	 */
	public function filter_tables_list( $tables ) {
		$schemas = $this->get_registered_table_schemas();
		foreach ( $schemas as $class ) {
			$table_name            = $class::table_name( true );
			$tables[ $table_name ] = $table_name;
		}

		return $tables;
	}

	/**
	 * Registers the custom table names as properties on the `wpdb` global.
	 *
	 * @since 1.0.0
	 */
	public function register_custom_tables_names() {
		global $wpdb;
		$schemas = $this->get_registered_table_schemas();

		foreach ( $schemas as $class ) {
			$no_prefix_table_name          = $class::table_name( false );
			$prefixed_table_name           = $class::table_name( true );
			$wpdb->{$no_prefix_table_name} = $prefixed_table_name;
			if ( ! in_array( $wpdb->{$no_prefix_table_name}, $wpdb->tables, true ) ) {
				$wpdb->tables[] = $no_prefix_table_name;
			}
		}
	}

	/**
	 * Creates or updates the custom tables the plugin will use.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $force Whether to force the creation or update of the tables or not.
	 *
	 * @return array<mixed> A list of each creation or update result.
	 */
	public function up( $force = false ) {
		try {
			$this->db::table( 'posts' )->select ( 1 )->limit( 1 )->get();
		} catch ( \Exception $e ) {
			// Let's not try to create the tables on a blog that's missing the basic ones.
			return [];
		}

		$results       = [];
		$table_schemas = $force ? $this->get_registered_table_schemas() : $this->get_table_schemas_that_need_updates();

		// Get all registered table classes.
		foreach ( $table_schemas as $table_schema ) {
			/** @var Table_Schema_Interface $table_schema */
			$results[ $table_schema::table_name() ] = $table_schema->update();
		}

		$this->register_custom_tables_names();

		return count( $results ) ? array_merge( ...array_values( $results ) ) : [];
	}

	/**
	 * A proxy method to update the tables without forcing
	 * them.
	 *
	 * If the `update_tables` was directly hooked to the blog
	 * switches, then the blog ID, a positive integer, would be
	 * cast to a truthy value and force the table updates when
	 * not really required to.
	 *
	 * @since 1.0.0
	 *
	 * @return array<mixed> A list of each creation or update result.
	 */
	public function update_blog_tables() {
		return $this->up( false );
	}
}
