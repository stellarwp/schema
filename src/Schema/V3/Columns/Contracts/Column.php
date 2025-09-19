<?php
/**
 * The interface for the column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns\Contracts;

use StellarWP\Schema\V3\Traits\Indexable as Indexable_Trait;
use InvalidArgumentException;

/**
 * Class Column
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns\Contracts
 */
abstract class Column implements Column_Interface, Indexable {
	use Indexable_Trait;

	/**
	 * The reserved defaults for the SQL.
	 *
	 * @since TBD
	 *
	 * @var string[]
	 */
	public const SQL_RESERVED_DEFAULTS = [
		'CURRENT_TIMESTAMP',
		'CURRENT_DATE',
		'CURRENT_TIME',
	];

	/**
	 * The type of the column.
	 *
	 * @var string
	 */
	protected string $type;

	/**
	 * The PHP type of the column.
	 *
	 * @var string
	 */
	protected string $php_type;

	/**
	 * The name of the column.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Whether the column can be null.
	 *
	 * @var bool
	 */
	protected bool $nullable = false;

	/**
	 * The default value of the column.
	 *
	 * @var mixed
	 */
	protected $default = null;

	/**
	 * The on update value of the column.
	 *
	 * @var ?string
	 */
	protected ?string $on_update = null;

	/**
	 * Constructor.
	 *
	 * @param string $name The name of the column.
	 */
	public function __construct( string $name ) {
		$this->name = $name;
	}

	/**
	 * Get the type of the column.
	 *
	 * @return string The type of the column.
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get the PHP type of the column.
	 *
	 * @return string The PHP type of the column.
	 */
	public function get_php_type(): string {
		return $this->php_type;
	}

	/**
	 * Get the name of the column.
	 *
	 * @return string The name of the column.
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the nullable of the column.
	 *
	 * @return bool Whether the column can be null.
	 */
	public function get_nullable(): bool {
		return $this->nullable;
	}

	/**
	 * Get the default of the column.
	 *
	 * @return mixed The default value of the column.
	 */
	public function get_default(): mixed {
		return $this->default;
	}

	/**
	 * Get the on update value of the column.
	 *
	 * @return ?string The on update value of the column.
	 */
	public function get_on_update(): ?string {
		return $this->on_update;
	}

	/**
	 * Set the type of the column.
	 *
	 * @param string $type The type of the column.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the type is not valid.
	 */
	public function set_type( string $type ): self {
		if ( ! in_array( $type, $this->get_supported_column_types(), true ) ) {
			throw new InvalidArgumentException( 'Invalid column type `' . $type . '` for class `' . get_class( $this ) . '`.' );
		}
		$this->type = $type;
		return $this;
	}

	/**
	 * Set the PHP type of the column.
	 *
	 * @param string $php_type The PHP type of the column.
	 *
	 * @return self
	 *
	 * @throws InvalidArgumentException If the PHP type is not valid.
	 */
	public function set_php_type( string $php_type ): self {
		if ( ! in_array( $php_type, $this->get_supported_php_types(), true ) ) {
			throw new InvalidArgumentException( 'Invalid PHP type `' . $php_type . '` for class `' . get_class( $this ) . '`.' );
		}
		$this->php_type = $php_type;
		return $this;
	}

	/**
	 * Set the name of the column.
	 *
	 * @param string $name The name of the column.
	 *
	 * @return self
	 */
	public function set_name( string $name ): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Set the default of the column.
	 *
	 * @param mixed $default The default value of the column.
	 *
	 * @return self
	 */
	public function set_default( mixed $default ): self {
		$this->default = $default;
		return $this;
	}

	/**
	 * Set the nullable of the column.
	 *
	 * @param bool $nullable Whether the column can be null.
	 *
	 * @return self
	 */
	public function set_nullable( bool $nullable ): self {
		$this->nullable = $nullable;
		return $this;
	}

	/**
	 * Set the on update value of the column.
	 *
	 * @param ?string $on_update The on update value of the column.
	 *
	 * @return self
	 */
	public function set_on_update( ?string $on_update ): self {
		$this->on_update = $on_update;
		return $this;
	}

	/**
	 * Get the definition of the column.
	 *
	 * @return string The definition of the column.
	 */
	public function get_definition(): string {
		$sql = "`{$this->get_name()}` {$this->get_type()}";

		if ( $this instanceof Lengthable && $this instanceof Precisionable ) {
			$sql .= "({$this->get_length()}, {$this->get_precision()})";
		} elseif ( $this instanceof Lengthable ) {
			$sql .= "({$this->get_length()})";
		}

		if ( $this instanceof Signable && ! $this->get_signed() ) {
			$sql .= ' UNSIGNED';
		}

		if ( $this instanceof Precisionable ) {
			$sql .= "({$this->get_precision()})";
		}

		$sql .= $this->get_nullable() ? ' NULL' : ' NOT NULL';

		if ( $this instanceof Auto_Incrementable && $this->get_auto_increment() ) {
			$sql .= ' AUTO_INCREMENT';
		}

		if ( $this->get_default() ) {
			$default = $this->get_default();
			$sql .= ' DEFAULT ' . ( in_array( $default, self::SQL_RESERVED_DEFAULTS, true ) || in_array( $this->get_type(), [ self::PHP_TYPE_INT, self::PHP_TYPE_BOOL, self::PHP_TYPE_FLOAT ], true ) ? $default : "'{$default}'" );
		}

		if ( $this->get_on_update() ) {
			$sql .= ' ON UPDATE ' . $this->get_on_update();
		}

		return $sql;
	}

	/**
	 * Get the supported column types.
	 *
	 * @return string[] The supported column types.
	 */
	protected function get_supported_column_types(): array {
		return self::SUPPORTED_COLUMN_TYPES;
	}

	/**
	 * Get the supported PHP types.
	 *
	 * @return string[] The supported PHP types.
	 */
	protected function get_supported_php_types(): array {
		return self::SUPPORTED_PHP_TYPES;
	}
}
