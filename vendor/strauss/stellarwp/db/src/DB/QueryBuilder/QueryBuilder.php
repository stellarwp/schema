<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder;

use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\Aggregate;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\CRUD;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\FromClause;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\GroupByStatement;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\HavingClause;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\JoinClause;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\LimitStatement;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\MetaQuery;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\OffsetStatement;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\OrderByStatement;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\SelectStatement;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\TablePrefix;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\UnionOperator;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns\WhereClause;

/**
 * @since 1.0.0
 */
class QueryBuilder {
	use Aggregate;
	use CRUD;
	use FromClause;
	use GroupByStatement;
	use HavingClause;
	use JoinClause;
	use LimitStatement;
	use MetaQuery;
	use OffsetStatement;
	use OrderByStatement;
	use SelectStatement;
	use TablePrefix;
	use UnionOperator;
	use WhereClause;

	/**
	 * @return string
	 */
	public function getSQL() {
		$sql = array_merge(
			$this->getSelectSQL(),
			$this->getFromSQL(),
			$this->getJoinSQL(),
			$this->getWhereSQL(),
			$this->getGroupBySQL(),
			$this->getHavingSQL(),
			$this->getOrderBySQL(),
			$this->getLimitSQL(),
			$this->getOffsetSQL(),
			$this->getUnionSQL()
		);

		// Trim double spaces added by DB::prepare
		return str_replace(
			[ '   ', '  ' ],
			' ',
			implode( ' ', $sql )
		);
	}
}
