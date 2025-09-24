<?php
/**
 * The interface for the Referenced_ID column.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

/**
 * Class Referenced_ID
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Columns
 */
class Referenced_ID extends Integer_Column {
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
	protected bool $auto_increment = false;

	/**
	 * Whether the column is a primary key.
	 *
	 * @var bool
	 */
	protected bool $is_index = true;
}
