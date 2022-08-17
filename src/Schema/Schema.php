<?php

namespace StellarWP\Schema;

use lucatume\DI52\ServiceProvider as Service_Provider;

class Schema extends Service_Provider {
	/**
	 * Initializes the service provider.
	 *
	 * @since 1.0.0
	 */
	public static function init() : void {
		Container::init()->register( static::class );
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( Builder\Schema_Builder::class, Builder\Schema_Builder::class );
		$this->container->singleton( Fields\Collection::class, Fields\Collection::class );
		$this->container->singleton( Tables\Collection::class, Tables\Collection::class );

		/**
		 * These providers should be the ones that extend the bulk of features for CT1,
		 * with only the bare minimum of providers registered above, to determine important state information.
		 */
		$this->container->register( Full_Activation_Provider::class );
		// Set a flag in the container to indicate there was a full activation of the CT1 component.
		$this->container->setVar( 'stellarwp_schema_fully_activated', true );

		$this->register_hooks();
	}

	/**
	 * Registers all hooks.
	 *
	 * @since 1.0.0
	 */
	private function register_hooks() : void {
		/**
		 * Filters the priority of the plugins_loaded action for running Builder\Schema_Builder::up.
		 *
		 * @param int $priority The priority of the action.
		 */
		$priority = apply_filters( 'stellarwp_schema_up_plugins_loaded_priority', 1000 );

		add_action( 'plugins_loaded', $this->container->callback( Builder\Schema_Builder::class, 'up' ), $priority, 0 );
	}
}
