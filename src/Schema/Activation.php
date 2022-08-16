<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   TBD
 *
 * @package StellarWP\WPTables
 */

namespace StellarWP\Schema;

use StellarWP\Schema\Builder\Schema_Builder;

/**
 * Class Activation
 *
 * @since   TBD
 *
 * @package StellarWP\WPTables
 */
class Activation {
	/**
	 * The name of the transient that will be used to flag whether the library activated
	 * or not.
	 *
	 * @since TBD
	 */
	const ACTIVATION_TRANSIENT = 'stellar_schema_builder_initialized';

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
		$db_hash = get_transient( static::ACTIVATION_TRANSIENT );

		$container = Container::init();

		$schema_builder = $container->make( Schema_Builder::class );
		$hash = $schema_builder->get_registered_schemas_version_hash();

		if ( $db_hash == $hash ) {
			return;
		}

		set_transient( static::ACTIVATION_TRANSIENT, $hash, DAY_IN_SECONDS );

		// Sync any schema changes we may have.
		if ( $schema_builder->all_tables_exist() ) {
			$schema_builder->up();
		}

		if ( ! Container::init()->getVar( 'stellarwp_schema_fully_activated' ) ) {
			/**
			 * On new installations the full activation code will find an empty state and
			 * will have not activated at this point, do it now if required.
			 */
			Container::init()->register( Full_Activation_Provider::class );
		}
	}

	/**
	 * Handles the feature deactivation.
	 *
	 * @since TBD
	 */
	public static function deactivate() {
		$services = Container::init();

		// @todo Should we drop the tables here, gracefully, if no data was generated?
		$services->make( Schema_Builder::class )->clean();
	}
}
