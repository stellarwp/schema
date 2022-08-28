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
trait OffsetStatement {
	/**
	 * @var int
	 */
	protected $offset;

	/**
	 * @param  int  $offset
	 *
	 * @return $this
	 */
	public function offset( $offset ) {
		$this->offset = (int) $offset;

		return $this;
	}

	protected function getOffsetSQL() {
		return $this->limit && $this->offset
			? [ "OFFSET {$this->offset}" ]
			: [];
	}
}
