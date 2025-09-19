<?php
/**
 * The interface for the ID column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Integer_Column;

/**
 * Class ID
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
class ID extends Integer_Column {
	/**
	 * Whether the column is signed.
	 *
	 * @var bool
	 */
	protected bool $signed = false;

	/**
	 * Whether the column is auto increment.
	 *
	 * @var bool
	 */
	protected bool $auto_increment = true;

	/**
	 * The name of the column.
	 *
	 * @var string
	 */
	protected string $name = 'id';
}
