<?php

namespace StellarWP\Schema;

use StellarWP\ContainerContract\ContainerInterface;

class Config {
	/**
	 * Container object.
	 *
	 * @var ContainerInterface
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
	 * @return ContainerInterface
	 */
	public static function get_container() : ContainerInterface {
		if ( self::$container === null ) {
			throw new \RuntimeException( 'You must provide a container via StellarWP\Schema\Config::set_container() before attempting to fetch it.' );
		}

		return self::$container;
	}

	/**
	 * Get the StellarWP\DB class.
	 *
	 * @return string
	 */
	public static function get_db() : string {
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
	public static function has_container() : bool {
		return self::$container !== null;
	}

	/**
	 * Returns whether the db has been set.
	 *
	 * @return bool
	 */
	public static function has_db() : bool {
		return self::$db_class !== null;
	}

	/**
	 * Set the container object.
	 *
	 * @param ContainerInterface $container Container object.
	 */
	public static function set_container( ContainerInterface $container ) {
		self::$container = $container;
	}

	/**
	 * Set the StellarWP\DB class.
	 *
	 * @param string $db_class StellarWP\DB class.
	 */
	public static function set_db( string $db_class ) {
		self::$db_class = $db_class;
	}
}
