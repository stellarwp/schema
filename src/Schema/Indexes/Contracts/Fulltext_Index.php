<?php
/**
 * The class for the fulltext index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes\Contracts;

/**
 * Class Fulltext_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes\Contracts
 */
abstract class Fulltext_Index extends Abstract_Index {
	/**
	 * The type of the fulltext index.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_FULLTEXT;
}
