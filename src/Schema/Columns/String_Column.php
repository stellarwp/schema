<?php
/**
 * The interface for the string column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Columns\Contracts\Lengthable;
use StellarWP\Schema\Columns\Contracts\Uniquable;
use StellarWP\Schema\Columns\Contracts\Primarable;
use StellarWP\Schema\Columns\PHP_Types;
use StellarWP\Schema\Columns\Column_Types;

/**
 * Class String_Column
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class String_Column extends Column implements Lengthable, Uniquable, Primarable {
	/**
	 * The length of the column.
	 *
	 * @var int
	 */
	private int $length = 255;

	/**
	 * The default value of the column.
	 *
	 * @var string
	 */
	protected $default = null;

	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = Column_Types::VARCHAR;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = PHP_Types::STRING;

	/**
	 * Get the length of the column.
	 *
	 * @return int The length of the column.
	 */
	public function get_length(): int {
		return max( min( $this->length, ! $this->is_index() ? 1024 : 191 ), 1 );
	}

	/**
	 * Set the length of the column.
	 *
	 * @param int $length The length of the column.
	 *
	 * @return self
	 */
	public function set_length( int $length ): self {
		$this->length = $length;
		return $this;
	}

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return Column_Types::SUPPORTED_STRING;
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
