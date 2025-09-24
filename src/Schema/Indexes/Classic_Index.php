<?php
/**
 * The class for the classic index.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Indexes
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes;

use StellarWP\Schema\Indexes\Contracts\Abstract_Index;

/**
 * Class Classic_Index
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Indexes
 */
class Classic_Index extends Abstract_Index {
	/**
	 * The type of the classic index.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_INDEX;
}
