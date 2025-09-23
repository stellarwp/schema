<?php
/**
 * The class for the PHP types.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use DateTimeInterface;

/**
 * Class PHP_Types
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
final class PHP_Types {
	/**
	 * The PHP type for an integer.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const INT = 'int';

	/**
	 * The PHP type for a string.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const STRING = 'string';

	/**
	 * The PHP type for a json.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const JSON = 'json';

	/**
	 * The PHP type for a float.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const FLOAT = 'float';

	/**
	 * The PHP type for a boolean.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const BOOL = 'bool';

	/**
	 * The PHP type for a datetime.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DATETIME = DateTimeInterface::class;

	/**
	 * The PHP type for a datetime.
	 *
	 * @since TBD
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
