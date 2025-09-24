<?php
/**
 * The interface for the datetime column.
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
 * Class Datetime_Column
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class Datetime_Column extends Column {
	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = Column_Types::TIMESTAMP;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = PHP_Types::DATETIME;

	/**
	 * Get the default value of the column.
	 *
	 * @return mixed The default value of the column.
	 *
	 * @throws InvalidArgumentException If the default value is not valid.
	 */
	public function get_default() {
		if ( 'CURRENT_TIMESTAMP' === $this->default && $this->get_type() !== Column_Types::TIMESTAMP ) {
			throw new InvalidArgumentException( 'CURRENT_TIMESTAMP is not a valid default for a non timestamp column until MySQL 5.6.5. Please use a timestamp column instead.' );
		}

		return $this->default;
	}

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return Column_Types::SUPPORTED_DATETIME;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			PHP_Types::DATETIME
		];
	}
}
