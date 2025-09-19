<?php
/**
 * The class for the classic index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes\Contracts;

/**
 * Class Classic_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */
abstract class Classic_Index extends Abstract_Index {
	/**
	 * The type of the classic index.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_INDEX;
}
