<?php

namespace StellarWP\Schema;

/**
 * A helper class for registering StellarWP Schema resources.
 */
class Register {
	/**
	 * Register a plugin resource.
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

		return Container::init()->make( Tables\Collection::class )->add( $table );
	}
}
