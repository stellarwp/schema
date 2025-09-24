<?php
/**
 * The class for the PHP types.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use DateTimeInterface;

/**
 * Class PHP_Types
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
final class PHP_Types {
	/**
	 * The PHP type for an integer.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const INT = 'int';

	/**
	 * The PHP type for a string.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const STRING = 'string';

	/**
	 * The PHP type for a json.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const JSON = 'json';

	/**
	 * The PHP type for a float.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const FLOAT = 'float';

	/**
	 * The PHP type for a boolean.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const BOOL = 'bool';

	/**
	 * The PHP type for a datetime.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public const DATETIME = DateTimeInterface::class;

	/**
	 * The PHP type for a datetime.
	 *
	 * @since 3.0.0
	 *
	 * @var string[]
	 */
	public const SUPPORTED = [
		self::INT,
		self::STRING,
		self::JSON,
		self::FLOAT,
		self::BOOL,
		self::DATETIME,
	];
}
