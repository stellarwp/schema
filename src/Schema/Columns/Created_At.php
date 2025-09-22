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

use StellarWP\Schema\Columns\Datetime_Column;

/**
 * Class Created_At
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Columns
 */
class Created_At extends Datetime_Column {
	/**
	 * The default value of the column.
	 *
	 * @var string
	 */
	protected $default = 'CURRENT_TIMESTAMP';
}
