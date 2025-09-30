<?php
/**
 * The class for the blob column.
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
 * Class Blob_Column
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class Blob_Column extends Text_Column {
	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = Column_Types::BLOB;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = PHP_Types::BLOB;

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return Column_Types::SUPPORTED_BLOB;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			PHP_Types::BLOB,
			PHP_Types::STRING,
			PHP_Types::JSON,
		];
	}
}
