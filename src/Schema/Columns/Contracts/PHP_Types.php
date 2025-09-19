<?php
/**
 * The interface for the PHP types.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

use DateTimeInterface;

/**
 * Interface PHP_Types
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
interface PHP_Types {
	/**
	 * The PHP type for an integer.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PHP_TYPE_INT = 'int';

	/**
	 * The PHP type for a string.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PHP_TYPE_STRING = 'string';

	/**
	 * The PHP type for a json.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PHP_TYPE_JSON = 'json';

	/**
	 * The PHP type for a float.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PHP_TYPE_FLOAT = 'float';

	/**
	 * The PHP type for a boolean.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PHP_TYPE_BOOL = 'bool';

	/**
	 * The PHP type for a datetime.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const PHP_TYPE_DATETIME = DateTimeInterface::class;

	/**
	 * The PHP type for a datetime.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SUPPORTED_PHP_TYPES = [
		self::PHP_TYPE_INT,
		self::PHP_TYPE_STRING,
		self::PHP_TYPE_JSON,
		self::PHP_TYPE_FLOAT,
		self::PHP_TYPE_BOOL,
		self::PHP_TYPE_DATETIME,
	];
}
