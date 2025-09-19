<?php
/**
 * The interface for the created at column.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Columns;

use StellarWP\Schema\Columns\Contracts\Datetime_Column;

/**
 * Class Created_At
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
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
