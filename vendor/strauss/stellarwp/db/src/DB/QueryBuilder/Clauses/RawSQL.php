<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses;

use StellarWP\Schema\StellarWP\DB\DB;

/**
 * @since 1.0.0
 */
class RawSQL {
	/**
	 * @var string
	 */
	public $sql;

	/**
	 * @param  string  $sql
	 * @param  array<int,mixed>|string|null  $args
	 */
	public function __construct( $sql, $args = null ) {
		$this->sql = $args ? DB::prepare( $sql, $args ) : $sql;
	}
}
