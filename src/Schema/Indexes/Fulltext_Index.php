<?php
/**
 * The class for the fulltext index.
 *
 * @since 3.0.0
 *
 * @package StellarWP\Schema\Indexes
 */

declare( strict_types=1 );

namespace StellarWP\Schema\Indexes;

use StellarWP\Schema\Indexes\Contracts\Abstract_Index;

/**
 * Class Fulltext_Index
 *
 * Fulltext indexes are only supporting in MySQL 5.6+.
 *
 * They should be avoided for projects that are usable by the whole WP community for now.
 *
 * @since 3.0.0
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
