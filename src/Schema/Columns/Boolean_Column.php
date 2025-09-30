<?php
/**
 * The class for the boolean column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Columns\Column_Types;

/**
 * Class Boolean_Column
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class Boolean_Column extends Column {
	/**
	 * The type of the column.
	 * Boolean is typically stored as TINYINT(1) in MySQL.
	 *
	 * @var string
	 */
	protected string $type = Column_Types::BOOLEAN;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = PHP_Types::BOOL;

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return Column_Types::SUPPORTED_BOOLEAN;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			PHP_Types::BOOL,
		];
	}
}
