<?php
/**
 * The class for the primary key.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes\Contracts;

/**
 * Class Primary_Key
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */
abstract class Primary_Key extends Abstract_Index {
	/**
	 * The type of the primary key.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_PRIMARY;
}
