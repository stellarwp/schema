<?php

namespace StellarWP\WPTables;

class WPTables extends \tad_DI52_ServiceProvider {
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
		$this->register_hooks();
	}

	/**
	 * Registers all hooks.
	 *
	 * @since 1.0.0
	 */
	private function register_hooks() : void {
	}
}
