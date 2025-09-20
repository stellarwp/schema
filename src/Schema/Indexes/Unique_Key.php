<?php
/**
 * The class for the unique key.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes;

use StellarWP\Schema\Indexes\Contracts\Abstract_Index;

/**
 * Class Unique_Key
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes
 */
class Unique_Key extends Abstract_Index {
	/**
	 * The type of the unique key.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_UNIQUE;
}
