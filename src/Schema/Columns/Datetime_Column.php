<?php
/**
 * The interface for the datetime column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Column;

/**
 * Class Datetime_Column
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
class Datetime_Column extends Column {
	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = self::COLUMN_TYPE_TIMESTAMP;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = self::PHP_TYPE_DATETIME;

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return self::SUPPORTED_DATETIME_COLUMN_TYPES;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			self::PHP_TYPE_DATETIME
		];
	}
}
