<?php

namespace StellarWP\Schema\Migrations\Contracts;

use lucatume\DI52\App;
use lucatume\DI52\Container;
use RuntimeException;

/**
 * Class Migration
 *
 * Extend this class when create database migration. up and timestamp are required member functions
 *
 * @since 1.2.0
 */
abstract class Migration {
	/**
	 * @var string The migration id.
	 */
	protected static $id;

	/**
	 * @var Container The dependency injection container.
	 */
	protected $container;

	/**
	 * @var string The organizational group this table belongs to.
	 */
	protected static $group = '';

	/**
	 * @var string The migration timestamp.
	 */
	protected static $timestamp;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The container to use.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: App::container();

		if ( ! static::$id ) {
			throw new RuntimeException( 'A unique ' . __CLASS__ . '::$id must be defined for the migration.' );
		}

		if ( ! static::$timestamp ) {
			throw new RuntimeException( __CLASS__ . '::$timestamp must be defined as a valid unix timestamp.' );
		}
	}

	/**
	 * Return a unique identifier for the migration
	 *
	 * @return string
	 */
	public static function id() {
		return static::$id;
	}

	/**
	 * Bootstrap migration logic.
	 *
	 * @since 1.2.0
	 */
	abstract public function run();

	/**
	 * Return a Unix Timestamp for when the migration was created
	 *
	 * Example: strtotime( '2020-09-16 12:30:00')
	 *
	 * @since 1.2.0
	 *
	 * @return int Unix timestamp for when the migration was created
	 */
	public static function timestamp() {
		return static::$timestamp;
	}

	/**
	 * Return migration title
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function title() {
		return static::id();
	}

	/**
	 * Return migration source
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function get_group() {
		return static::$group;
	}
}
