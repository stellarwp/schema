<?php
/**
 * The interface for the updated at column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns;

use StellarWP\Schema\V3\Columns\Contracts\Datetime_Column;

/**
 * Class Updated_At
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns
 */
class Updated_At extends Datetime_Column {
	/**
	 * The name of the column.
	 *
	 * @var string
	 */
	protected string $name = 'updated_at';

	/**
	 * Whether the column can be null.
	 *
	 * @var bool
	 */
	protected bool $nullable = true;

	/**
	 * The on update value of the column.
	 *
	 * @var ?string
	 */
	protected ?string $on_update = 'CURRENT_TIMESTAMP';
}
