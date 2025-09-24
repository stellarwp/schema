<?php
/**
 * The interface for the text column.
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
use InvalidArgumentException;

/**
 * Class Text_Column
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class Text_Column extends Column {
	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = Column_Types::TEXT;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = PHP_Types::STRING;

	/**
	 * Set the default value of the column.
	 *
	 * @param ?string $default The default value of the column.
	 *
	 * @throws InvalidArgumentException If the default value is not null.
	 */
	public function set_default( $default ): self {
		throw new InvalidArgumentException( 'The default value of a text column must be null.' );
	}

	/**
	 * Get the default value of the column.
	 *
	 * @return ?string The default value of the column.
	 */
	public function get_default(): ?string {
		return null;
	}

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return Column_Types::SUPPORTED_TEXT;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			PHP_Types::STRING,
			PHP_Types::JSON,
		];
	}
}
