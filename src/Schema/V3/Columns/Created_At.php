<?php
/**
 * The interface for the created at column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Columns;

use StellarWP\Schema\V3\Columns\Contracts\Datetime_Column;

/**
 * Class Created_At
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Columns
 */
class Created_At extends Datetime_Column {
	/**
	 * The name of the column.
	 *
	 * @var string
	 */
	protected string $name = 'created_at';

	/**
	 * The default value of the column.
	 *
	 * @var string
	 */
	protected string $default = 'CURRENT_TIMESTAMP';
}
