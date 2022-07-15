<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   TBD
 *
 * @package StellarWP\WPTables
 */

namespace StellarWP\WPTables;

use StellarWP\WPTables\Schema_Builder;

/**
 * Class Activation
 *
 * @since   TBD
 *
 * @package StellarWP\WPTables
 */
class Activation {
	/**
	 * The name of the transient that will be used to flag whether the plugin did activate
	 * or not.
	 *
	 * @since TBD
	 */
	const ACTIVATION_TRANSIENT = 'stellar_wptables_initialized';

	/**
	 * Handles the activation of the feature functions.
	 *
	 * @since TBD
	 */
	public static function activate() {
		$schema_builder = Container::init()->make( Schema_Builder::class);
		$schema_builder->up();
	}

	/**
	 * Checks the state to determine if whether we can create custom tables.
	 *
	 * This method will run once a day (using transients).
	 *
	 * @since TBD
	 */
	public static function init() {
		// Check if we ran recently.
		$initialized = get_transient( self::ACTIVATION_TRANSIENT );

		if ( $initialized ) {
			return;
		}

		set_transient( self::ACTIVATION_TRANSIENT, 1, DAY_IN_SECONDS );

		/** @var Container $services */
		$services       = Container::init();
		$schema_builder = $services->make( Schema_Builder::class );

		// Sync any schema changes we may have.
		if ( $schema_builder->all_tables_exist() ) {
			$schema_builder->up();
		}
	}
}
