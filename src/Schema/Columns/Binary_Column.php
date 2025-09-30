<?php
/**
 * The interface for the binary column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Columns\Column_Types;

/**
 * Class Binary_Column
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class Binary_Column extends String_Column {
	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = Column_Types::VARBINARY;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = PHP_Types::STRING;

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return Column_Types::SUPPORTED_BINARY;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			PHP_Types::STRING,
			PHP_Types::BLOB,
		];
	}
}
