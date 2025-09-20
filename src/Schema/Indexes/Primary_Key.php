<?php
/**
 * The class for the primary key.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes;

use StellarWP\Schema\Indexes\Contracts\Abstract_Index;

/**
 * Class Primary_Key
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes
 */
class Primary_Key extends Abstract_Index {
	/**
	 * The type of the primary key.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_PRIMARY;
}
