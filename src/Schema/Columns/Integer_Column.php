<?php
/**
 * The interface for the integer column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Column;
use StellarWP\Schema\Columns\Contracts\Lengthable;
use StellarWP\Schema\Columns\Contracts\Signable;
use StellarWP\Schema\Columns\Contracts\Auto_Incrementable;
use StellarWP\Schema\Columns\Contracts\Uniquable;

/**
 * Class Integer_Column
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
class Integer_Column extends Column implements Lengthable, Signable, Auto_Incrementable, Uniquable {
	/**
	 * The length of the column.
	 *
	 * @var int
	 */
	protected int $length = 20;

	/**
	 * Whether the column is signed.
	 *
	 * @var bool
	 */
	protected bool $signed = true;

	/**
	 * Whether the column is auto increment.
	 *
	 * @var bool
	 */
	protected bool $auto_increment = false;

	/**
	 * The default value of the column.
	 *
	 * @var int
	 */
	protected $default = null;

	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type = self::COLUMN_TYPE_BIGINT;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type = self::PHP_TYPE_INT;

	/**
	 * Get the auto increment of the column.
	 *
	 * @return bool Whether the column is auto increment.
	 */
	public function get_auto_increment(): bool {
		return $this->auto_increment;
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
	 * Set the auto increment of the column.
	 *
	 * @param bool $auto_increment Whether the column is auto increment.
	 *
	 * @return self
	 */
	public function set_auto_increment( bool $auto_increment ): self {
		$this->auto_increment = $auto_increment;
		$this->set_is_primary_key( true );
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
		return self::SUPPORTED_INTEGER_COLUMN_TYPES;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return [
			self::PHP_TYPE_INT,
			self::PHP_TYPE_BOOL,
		];
	}
}
