<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses;

/**
 * @since 1.0.0
 */
class Select {
	/**
	 * @var string
	 */
	public $column;

	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @param  string  $column
	 * @param  string|null  $alias
	 */
	public function __construct( $column, $alias = null ) {
		$this->column = trim( $column );
		$this->alias  = trim( $alias );
	}
}
