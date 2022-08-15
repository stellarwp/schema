<?php

namespace StellarWP\Schema\Builder;

use StellarWP\Schema\Container;
use StellarWP\Schema\Tables\Collection as Table_Collection;
use WP_CLI;

class Schema_Builder {
	/**
	 * Container.
	 *
	 * @var Container
	 */
	public $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container Container instance.
	 */
	public function __construct( Container $container = null ) {
		$this->container = $container ?: Container::init();
	}

	/**
	 * Get the registered table handlers that need updates.
	 *
	 * @since TBD
	 *
	 * @return array<Table_Schema_Interface>
	 */
	public function get_table_schemas_that_need_updates() {
		return $this->container->make( Table_Collection::class )->get_tables_needing_updates();
	}

	/**
	 * Get the registered table handlers.
	 *
	 * @since TBD
	 *
	 * @return array<Table_Schema_Interface>
	 */
	public function get_registered_table_schemas() {
		return $this->container->make( Table_Collection::class );
	}

	/**
	 * Trigger actions to drop the custom tables.
	 *
	 * @since TBD
	 */
	public function down() {
		/**
		 * Runs before the custom tables are dropped by The Events Calendar.
		 *
		 * @since TBD
		 */
		do_action( 'stellarwp_pre_drop_tables' );

		$table_classes = $this->get_registered_table_schemas();

		/**
		 * Filters the tables to be dropped.
		 *
		 * @since TBD
		 *
		 * @param array<Custom_Table_Interface> $table_classes A list of Custom_Table_Interface objects that will have their tables dropped.
		 */
		$table_classes = apply_filters( 'stellarwp_tables_to_drop', $table_classes );

		foreach ( $table_classes as $table_class ) {
			$table_class->drop();
		}

		/**
		 * Runs after the custom tables have been dropped by The Events Calendar.
		 *
		 * @since TBD
		 */
		do_action( 'stellarwp_post_drop_tables' );
	}

	/**
	 * Filters the list of tables for a blog adding the ones created by the plugin.
	 *
	 * @since TBD
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
	 * A proxy method to update the tables without forcing
	 * them.
	 *
	 * If the `update_tables` was directly hooked to the blog
	 * switches, then the blog ID, a positive integer, would be
	 * cast to a truthy value and force the table updates when
	 * not really required to.
	 *
	 * @since TBD
	 *
	 * @return array<mixed> A list of each creation or update result.
	 */
	public function update_blog_tables() {
		return $this->up( false );
	}

	/**
	 * Creates or updates the custom tables the plugin will use.
	 *
	 * @since TBD
	 *
	 * @param bool $force Whether to force the creation or update of the tables or not.
	 *
	 * @return array<string,mixed> A list of each creation or update result.
	 */
	public function up( $force = false ) {
		global $wpdb;

		//phpcs:ignore
		$wpdb->get_results( "SELECT 1 FROM {$wpdb->posts} LIMIT 1" );
		$posts_table_exists = '' === $wpdb->last_error;
		// Let's not try to create the tables on a blog that's missing the basic ones.
		if ( ! $posts_table_exists ) {
			return [];
		}

		$results       = [];
		$table_schemas = $force ? $this->get_registered_table_schemas() : $this->get_table_schemas_that_need_updates();

		// Get all registered table classes.
		foreach ( $table_schemas as $table_schema ) {
			/** @var Table_Schema_Interface $table_schema */
			$results[ $table_schema::table_name() ] = $table_schema->update();
		}

		return count( $results ) ? array_merge( ...array_values( $results ) ) : [];
	}

	/**
	 * Registers the custom table names as properties on the `wpdb` global.
	 *
	 * @since TBD
	 */
	public function register_custom_tables_names() {
		global $wpdb;
		$schemas = $this->get_registered_table_schemas();

		foreach ( $schemas as $class ) {
			$no_prefix_table_name          = $class::table_name( false );
			$prefixed_tale_name            = $class::table_name( true );
			$wpdb->{$no_prefix_table_name} = $prefixed_tale_name;
			if ( ! in_array( $wpdb->{$no_prefix_table_name}, $wpdb->tables, true ) ) {
				$wpdb->tables[] = $no_prefix_table_name;
			}
		}
	}

	/**
	 * Empties the plugin custom tables.
	 *
	 * @since TBD
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
	 * Whether all the custom tables exist or not. Does not check custom fields.
	 *
	 * Note: the method will return `false` if even one table is missing.
	 *
	 * @since TBD
	 *
	 * @param string|null $group An optional group name to restrict the check to.
	 *
	 * @return bool Whether all custom tables exist or not. Does not check custom fields.
	 */
	public function all_tables_exist( $group = null ) {
		global $wpdb;
		$table_classes = $this->get_registered_table_schemas();

		if ( null !== $group ) {
			$table_classes = array_filter( $table_classes, static function ( $class ) use ( $group ) {
				return $class::group_name() === $group;
			} );
		}

		if ( empty( $table_classes ) ) {
			// No table class was even found.
			return false;
		}

		$result        = $wpdb->get_col( 'SHOW TABLES' );
		foreach ( $table_classes as $class ) {
			if ( ! in_array( $class::table_name(), $result, true ) ) {

				return false;
			}
		}

		return true;
	}
}
