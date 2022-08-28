<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses;

use StellarWP\Schema\StellarWP\DB\QueryBuilder\Types\Math;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Types\Operator;
use InvalidArgumentException;

/**
 * @since 1.0.0
 */
class Having {
	/**
	 * @var string
	 */
	public $column;

	/**
	 * @var string
	 */
	public $comparisonOperator;

	/**
	 * @var string|int
	 */
	public $value;

	/**
	 * @var string
	 */
	public $logicalOperator;

	/**
	 * @var string|null
	 */
	public $mathFunction;

	/**
	 * @param  string  $column
	 * @param  string  $comparisonOperator
	 * @param  string|int  $value
	 * @param  string|null  $logicalOperator
	 * @param  string  $mathFunction
	 */
	public function __construct( $column, $comparisonOperator, $value, $logicalOperator, $mathFunction = null ) {
		$this->column			 = trim( $column );
		$this->comparisonOperator = $this->getComparisonOperator( $comparisonOperator );
		$this->value			  = $value;
		$this->logicalOperator	= $logicalOperator ? $this->getLogicalOperator( $logicalOperator ) : '';
		$this->mathFunction	   = $this->getMathFunction( $mathFunction );
	}

	/**
	 * @param  string  $logicalOperator
	 *
	 * @return string
	 */
	private function getLogicalOperator( $logicalOperator ) {
		$operators = [
			Operator::_AND,
			Operator::_OR
		];

		$logicalOperator = strtoupper( $logicalOperator );

		if ( ! in_array( $logicalOperator, $operators, true ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Unsupported logical operator %s. Please use one of the supported operators (%s)',
					$logicalOperator,
					implode( ',', $operators )
				)
			);
		}

		return $logicalOperator;
	}

	/**
	 * @param  string  $comparisonOperator
	 *
	 * @return string
	 */
	private function getComparisonOperator( $comparisonOperator ) {
		$operators = [
			'<',
			'<=',
			'>',
			'>=',
			'<>',
			'!=',
			'='
		];

		if ( ! in_array( $comparisonOperator, $operators, true ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Unsupported comparison operator %s. Please use one of the supported operators (%s)',
					$comparisonOperator,
					implode( ',', $operators )
				)
			);
		}

		return $comparisonOperator;
	}


	/**
	 * @param  string  $mathFunction
	 *
	 * @return string|null
	 */
	private function getMathFunction( $mathFunction ) {
		if ( array_key_exists( $mathFunction, Math::getTypes() ) ) {
			return $mathFunction;
		}

		return null;
	}
}
