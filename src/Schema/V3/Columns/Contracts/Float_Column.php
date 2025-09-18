<?php
/**
 * The interface for the float column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

abstract class Float_Column extends Column implements Lengthable, Signable, Precisionable, Uniquable {
	/**
	 * The length of the column.
	 *
	 * @var int
	 */
	private int $length = 20;

	/**
	 * The precision of the column.
	 *
	 * @var int
	 */
	private int $precision = 2;

	/**
	 * Whether the column is signed.
	 *
	 * @var bool
	 */
	private bool $signed = true;

	/**
	 * The default value of the column.
	 *
	 * @var int
	 */
	protected ?int $default = null;

	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = self::COLUMN_TYPE_FLOAT;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = self::PHP_TYPE_FLOAT;

	/**
	 * Get the precision of the column.
	 *
	 * @return int The precision of the column.
	 */
	public function get_precision(): int {
		return $this->precision;
	}

	/**
	 * Get the signed of the column.
	 *
	 * @return bool Whether the column is signed.
	 */
	public function get_signed(): bool {
		return $this->signed;
	}

	/**
	 * Get the length of the column.
	 *
	 * @return int The length of the column.
	 */
	public function get_length(): int {
		return $this->length;
	}

	/**
	 * Set the precision of the column.
	 *
	 * @param int $precision The precision of the column.
	 *
	 * @return self
	 */
	public function set_precision( int $precision ): self {
		$this->precision = $precision;
		return $this;
	}

	/**
	 * Set the signed of the column.
	 *
	 * @param bool $signed Whether the column is signed.
	 *
	 * @return self
	 */
	public function set_signed( bool $signed ): self {
		$this->signed = $signed;
		return $this;
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
		return self::SUPPORTED_FLOAT_COLUMN_TYPES;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			self::PHP_TYPE_FLOAT,
		];
	}
}
