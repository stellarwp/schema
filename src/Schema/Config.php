<?php

namespace StellarWP\Schema;

class Config {
	/**
	 * Container object.
	 *
	 * @var object
	 */
	private static $container;

	/**
	 * StellarWP\DB class.
	 *
	 * @var string
	 */
	private static $db_class;

	/**
	 * Get the container.
	 *
	 * @return object|null
	 */
	public static function get_container() {
		if ( self::$container === null ) {
			throw new \RuntimeException( 'You must provide a container via StellarWP\Schema\Config::set_container() before attempting to fetch it.' );
		}

		return self::$container;
	}

	/**
	 * Get the StellarWP\DB class.
	 *
	 * @return string|null
	 */
	public static function get_db() {
		if ( self::$db_class === null ) {
			throw new \RuntimeException( 'You must provide a StellarWP\DB\DB::class via StellarWP\Schema\Config::set_db() before attempting to fetch it.' );
		}

		return self::$db_class;
	}

	/**
	 * Returns whether the container has been set.
	 *
	 * @return bool
	 */
	public static function has_container() {
		return self::$container !== null;
	}

	/**
	 * Returns whether the db has been set.
	 *
	 * @return bool
	 */
	public static function has_db() {
		return self::$db_class !== null;
	}

	/**
	 * Set the container object.
	 */
	public static function set_container( $container ) {
		self::$container = $container;
	}

	/**
	 * Set the StellarWP\DB class.
	 *
	 * @param string $db_class StellarWP\DB class.
	 */
	public static function set_db( $db_class ) {
		self::$db_class = $db_class;
	}
}
