<?php
/**
 * The class for the classic index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Indexes\Contracts;

/**
 * Class Classic_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */
abstract class Classic_Index extends Abstract_Index {
	/**
	 * The type of the classic index.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_INDEX;
}
