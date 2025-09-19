<?php
/**
 * The interface for the last changed column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Datetime_Column;

/**
 * Class Last_Changed
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
class Last_Changed extends Datetime_Column {
	/**
	 * The name of the column.
	 *
	 * @var string
	 */
	protected string $name = 'last_changed';

	/**
	 * The default value of the column.
	 *
	 * @var string
	 */
	protected string $default = 'CURRENT_TIMESTAMP';

	/**
	 * The on update value of the column.
	 *
	 * @var ?string
	 */
	protected ?string $on_update = 'CURRENT_TIMESTAMP';
}
