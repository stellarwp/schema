<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   1.0.0
 *
 * @package StellarWP\WPTables
 */

namespace StellarWP\Schema;

use StellarWP\Schema\Builder;
use StellarWP\Schema\Config;

/**
 * Class Activation
 *
 * @since   1.0.0
 *
 * @package StellarWP\WPTables
 */
class Activation {
	/**
	 * The name of the transient that will be used to flag whether the library activated
	 * or not.
	 *
	 * @since 1.0.0
	 */
	const ACTIVATION_TRANSIENT = 'stellar_schema_builder_initialized';

	/**
	 * Handles the activation of the feature functions.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		$schema_builder = Config::get_container()->get( Builder::class);
		$schema_builder->up();
	}

	/**
	 * Checks the state to determine if whether we can create custom tables.
	 *
	 * This method will run once a day (using transients).
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		// Check if we ran recently.
		$db_hash = get_transient( static::ACTIVATION_TRANSIENT );

		$container = Config::get_container();

		$schema_builder = $container->get( Builder::class );
		$hash = $schema_builder->get_registered_schemas_version_hash();

		if ( $db_hash == $hash ) {
			return;
		}

		set_transient( static::ACTIVATION_TRANSIENT, $hash, DAY_IN_SECONDS );

		// Sync any schema changes we may have.
		if ( $schema_builder->all_tables_exist() ) {
			$schema_builder->up();
		}

		if (
			! $container->has( 'stellarwp_schema_fully_activated' )
			&& ! $container->get( 'stellarwp_schema_fully_activated' )
		) {
			/**
			 * On new installations the full activation code will find an empty state and
			 * will have not activated at this point, do it now if required.
			 */
			$container->singleton( Full_Activation_Provider::class, Full_Activation_Provider::class );
			$container->get( Full_Activation_Provider::class )->register();
		}
	}
}
