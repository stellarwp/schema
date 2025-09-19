<?php
/**
 * The class for the fulltext index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\V3\Indexes\Contracts;

/**
 * Class Fulltext_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\V3\Indexes\Contracts
 */
abstract class Fulltext_Index extends Abstract_Index {
	/**
	 * The type of the fulltext index.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_FULLTEXT;
}
