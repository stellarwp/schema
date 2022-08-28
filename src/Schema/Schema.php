<?php

namespace StellarWP\Schema;

use lucatume\DI52\App;
use lucatume\DI52\ServiceProvider;

require_once dirname( dirname( __DIR__ ) ) . '/vendor/strauss/autoload.php';

class Schema extends ServiceProvider {
	const VERSION = '1.1.0';

	/**
	 * Gets the Builder.
	 *
	 * @since 1.0.0
	 *
	 * @return Builder
	 */
	public static function builder() {
		static::init();

		return App::make( Builder::class );
	}

	/**
	 * Gets the field collection.
	 *
	 * @since 1.0.0
	 *
	 * @return Fields\Collection
	 */
	public static function fields() {
		static::init();

		return App::make( Fields\Collection::class );
	}

	/**
	 * Initializes the service provider.
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {
		App::container();

		if ( App::getVar( 'stellarwp_schema_registered', false ) ) {
			return;
		}

		App::register( static::class );
		App::setVar( 'stellarwp_schema_registered', true );
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		App::singleton( static::class, $this );
		App::singleton( Builder::class, Builder::class );
		App::singleton( Fields\Collection::class, Fields\Collection::class );
		App::singleton( Tables\Collection::class, Tables\Collection::class );

		/**
		 * These providers should be the ones that extend the bulk of features for CT1,
		 * with only the bare minimum of providers registered above, to determine important state information.
		 */
		App::register( Full_Activation_Provider::class );
		// Set a flag in the container to indicate there was a full activation of the CT1 component.
		App::setVar( 'stellarwp_schema_fully_activated', true );

		$this->register_hooks();
	}

	/**
	 * Registers all hooks.
	 *
	 * @since 1.0.0
	 */
	private function register_hooks() : void {
		if ( did_action( 'plugins_loaded' ) ) {
			App::make( Builder::class )->up();
		} else {
			/**
			 * Filters the priority of the plugins_loaded action for running Builder::up.
			 *
			 * @param int $priority The priority of the action.
			 */
			$priority = apply_filters( 'stellarwp_schema_up_plugins_loaded_priority', 1000 );

			add_action( 'plugins_loaded', App::callback( Builder::class, 'up' ), $priority, 0 );
		}
	}

	/**
	 * Gets the table collection.
	 *
	 * @since 1.0.0
	 *
	 * @return Tables\Collection
	 */
	public static function tables() {
		static::init();

		return App::make( Tables\Collection::class );
	}
}
