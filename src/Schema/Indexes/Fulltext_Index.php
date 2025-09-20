<?php
/**
 * The class for the fulltext index.
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes;

use StellarWP\Schema\Indexes\Contracts\Abstract_Index;

/**
 * Class Fulltext_Index
 *
 * @since TBD
 *
 * @package StellarWP\Schema\Indexes
 */
class Fulltext_Index extends Abstract_Index {
	/**
	 * The type of the fulltext index.
	 *
	 * @var string
	 */
	protected string $type = self::TYPE_FULLTEXT;
}
