<?php
/**
 * The class for the unique key.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Indexes\Contracts;

/**
 * Class Unique_Key
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */
abstract class Unique_Key extends Abstract_Index {
	/**
	 * The type of the unique key.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_UNIQUE;
}
