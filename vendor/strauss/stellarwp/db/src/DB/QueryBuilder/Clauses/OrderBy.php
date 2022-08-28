<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses;

use InvalidArgumentException;

/**
 * @since 1.0.0
 */
class OrderBy {
	/**
	 * @var string
	 */
	public $column;

	/**
	 * @var string
	 */
	public $direction;

	/**
	 * @param $column
	 * @param $direction
	 */
	public function __construct( $column, $direction ) {
		$this->column	= trim( $column );
		$this->direction = $this->getSortDirection( $direction );
	}

	/**
	 * @param  string  $direction
	 *
	 * @return string
	 */
	private function getSortDirection( $direction ) {
		$direction  = strtoupper( $direction );
		$directions = ['ASC', 'DESC'];

		if ( ! in_array( $direction, $directions, true ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Unsupported sort direction %s. Please use one of the (%s)',
					$direction,
					implode( ',', $directions )
				)
			);
		}

		return $direction;
	}
}
