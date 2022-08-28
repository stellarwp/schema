<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns;

use Closure;
use StellarWP\Schema\StellarWP\DB\DB;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\JoinQueryBuilder;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses\Join;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses\RawSQL;

/**
 * @since 1.0.0
 */
trait JoinClause {

	/**
	 * @var Closure[]|RawSQL[]
	 */
	protected $joins = [];

	/**
	 * Method used to build advanced JOIN queries, Check README.md for more info.
	 * If you need to perform only simple JOINs with only one JOIN condition, then you don't need this method.
	 *
	 * @param  Closure  $callback  The closure will receive a StellarWP\DB\QueryBuilder\JoinQueryBuilder instance
	 *
	 * @return static
	 */
	public function join( $callback ) {
		$this->joins[] = $callback;

		return $this;
	}

	/**
	 * @param  string|RawSQL  $table
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  string|null  $alias
	 *
	 * @return static
	 */
	public function leftJoin( $table, $column1, $column2, $alias = null ) {
		$this->join(
			function ( JoinQueryBuilder $builder ) use ( $table, $column1, $column2, $alias ) {
				$builder
					->leftJoin( $table, $alias )
					->on( $column1, $column2 );
			}
		);

		return $this;
	}

	/**
	 * @param  string|RawSQL  $table
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  string|null  $alias
	 *
	 * @return static
	 */
	public function innerJoin( $table, $column1, $column2, $alias = null ) {
		$this->join(
			function ( JoinQueryBuilder $builder ) use ( $table, $column1, $column2, $alias ) {
				$builder
					->innerJoin( $table, $alias )
					->on( $column1, $column2 );
			}
		);

		return $this;
	}

	/**
	 * @param  string|RawSQL  $table
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  string|null  $alias
	 *
	 * @return static
	 */
	public function rightJoin( $table, $column1, $column2, $alias = null ) {
		$this->join(
			function ( JoinQueryBuilder $builder ) use ( $table, $column1, $column2, $alias ) {
				$builder
					->rightJoin( $table, $alias )
					->on( $column1, $column2 );
			}
		);

		return $this;
	}


	/**
	 * Add raw SQL JOIN clause
	 *
	 * @param  string  $sql
	 * @param ...$args
	 *
	 * @return static
	 */
	public function joinRaw( $sql, ...$args ) {
		$this->joins[] = new RawSQL( $sql, $args );

		return $this;
	}


	/**
	 * @return string[]
	 */
	protected function getJoinSQL() {
		return array_map(function ( $callback ) {
			if ( $callback instanceof RawSQL ) {
				return $callback->sql;
			}

			$builder = new JoinQueryBuilder();

			call_user_func( $callback, $builder );

			$joins = array_map( function ( $join ) {
				if ( $join instanceof RawSQL ) {
					return $join->sql;
				}

				if ( $join instanceof Join ) {
					if ( $join->alias ) {
						return DB::prepare(
							'%1s JOIN %2s %3s',
							$join->joinType,
							$join->table,
							$join->alias
						);
					}

					return DB::prepare(
						'%1s JOIN %2s',
						$join->joinType,
						$join->table
					);
				}

				// JoinCondition
				return DB::prepare(
					$join->quote
						? ' %1s %2s = %s'
						: ' %1s %2s = %3s',
					$join->logicalOperator,
					$join->column1,
					$join->column2
				);
			}, $builder->getDefinedJoins() );

			return implode( ' ', $joins );
		}, $this->joins );
	}
}
