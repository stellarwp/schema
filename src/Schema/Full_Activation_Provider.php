<?php
/**
 * Responsible for registering providers that are only relevant after an appropriate number of steps have been taken to
 * fully activate the features of Custom Tables V1.
 *
 * Should not be registered if the Custom Tables have not been generated yet.
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema
 */

namespace StellarWP\Schema;

use StellarWP\Schema\Builder;
use WP_CLI;

/**
 * Class Full_Activation_Provider
 *
 * @since   1.0.0
 *
 * @package StellarWP\Schema
 */
class Full_Activation_Provider {
	/**
	 * Container object.
	 *
	 * @var object
	 */
	private $container;

	/**
	 * A flag property indicating whether the Service Provider did register or not.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Constructor.
	 *
	 * @param object $container
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Config::get_container();
	}

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;
		$this->container->bind( 'stellar_schema_fully_activated', static function() { return true; } );

		/*
		 * This block should be the only one capturing exceptions thrown in the context of
		 * the feature. This removes the burden of exception and error handling from the
		 * actual business code.
		 */
		try {
			$this->register_schema_hooks();
		} catch ( \Throwable $t ) {
			/**
			 * Fires an action when an error or exception happens in the
			 * context of custom table implementations.
			 *
			 * @since 1.0.0
			 *
			 * @param \Throwable $t The thrown error.
			 */
			do_action( 'stellarwp_schema_error', $t );
		}

		return true;
	}

	/**
	 * Registers the actions and filters required to have the custom table names available
	 * as properties on the `$wpdb` instance and to hook into some WP_CLI operations to
	 * empty the tables correctly.
	 *
	 * @since 1.0.0
	 */
	private function register_schema_hooks() {
		$schema_builder = $this->container->get( Builder::class );
		$schema_builder->register_custom_tables_names();

		if ( is_multisite() ) {
			add_action( 'activate_blog', [ $schema_builder, 'update_blog_tables' ] );
			add_action( 'activate_blog', [ $schema_builder, 'register_custom_tables_names' ] );
			add_action( 'switch_blog', [ $schema_builder, 'update_blog_tables' ] );
			add_action( 'switch_blog', [ $schema_builder, 'register_custom_tables_names' ] );
			add_filter( 'wpmu_drop_tables', [ $schema_builder, 'filter_tables_list' ] );
		}

		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::add_hook( 'after_invoke:site empty', [ $schema_builder, 'empty_custom_tables' ] );
		}
	}
}
