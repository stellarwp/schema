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
	 * Get the StellarWP\DB object.
	 *
	 * @return object|null
	 */
	public static function get_container() {
		return self::$container;
	}

	/**
	 * Get the StellarWP\DB class.
	 *
	 * @return string|null
	 */
	public static function get_db() {
		return self::$db_class;
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
