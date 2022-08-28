<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns;

/**
 * @since 1.0.0
 */
trait LimitStatement {
	/**
	 * @var int
	 */
	protected $limit;

	/**
	 * @param  int  $limit
	 *
	 * @return $this
	 */
	public function limit( $limit ) {
		$this->limit = (int) $limit;

		return $this;
	}

	protected function getLimitSQL() {
		return $this->limit
			? [ "LIMIT {$this->limit}" ]
			: [];
	}
}
