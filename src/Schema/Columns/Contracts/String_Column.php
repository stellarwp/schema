<?php
/**
 * The interface for the string column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns\Contracts;

/**
 * Class String_Column
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns\Contracts
 */
abstract class String_Column extends Column implements Lengthable, Uniquable {
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
	protected ?string $default = null;

	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = self::COLUMN_TYPE_VARCHAR;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = self::PHP_TYPE_STRING;

	/**
	 * Get the length of the column.
	 *
	 * @return int The length of the column.
	 */
	public function get_length(): int {
		return max( min( $this->length, ! $this->is_index() ? 255 : 191 ), 1 );
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
		return self::SUPPORTED_STRING_COLUMN_TYPES;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			self::PHP_TYPE_STRING,
			self::PHP_TYPE_JSON,
		];
	}
}
