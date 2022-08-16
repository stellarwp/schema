<?php

namespace StellarWP\Schema;

use StellarWP\Schema\Builder\Schema_Builder;

/**
 * A helper class for registering StellarWP Schema resources.
 */
class Register {
	/**
	 * Register a field schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field Field class.
	 *
	 * @return Builder\Abstract_Custom_Field
	 */
	public static function field( $field ) {
		if ( is_string( $field ) ) {
			$field = new $field();
		}

		$container = Container::init();

		$container->make( Fields\Collection::class )->add( $field );

		// If we've already executed plugins_loaded, automatically add the table.
		if ( did_action( 'plugins_loaded' ) ) {
			$container->make( Schema_Builder::class )->up();
		}

		return $field;
	}

	/**
	 * Register a table schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table Table class.
	 *
	 * @return Builder\Abstract_Custom_Table
	 */
	public static function table( $table ) {
		if ( is_string( $table ) ) {
			$table = new $table();
		}

		$container = Container::init();

		$container->make( Tables\Collection::class )->add( $table );

		// If we've already executed plugins_loaded, automatically add the table.
		if ( did_action( 'plugins_loaded' ) ) {
			$container->make( Schema_Builder::class )->up();
		}

		return $table;
	}
}
