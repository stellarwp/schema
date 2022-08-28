<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns;

use StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses\MetaTable;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\Clauses\RawSQL;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\JoinQueryBuilder;
use StellarWP\Schema\StellarWP\DB\QueryBuilder\QueryBuilder;

/**
 * @since 1.0.0
 */
trait MetaQuery {

	/**
	 * @var MetaTable[]
	 */
	private $metaTablesConfigs = [];

	/**
	 * @var string
	 */
	private $defaultMetaKeyColumn = 'meta_key';

	/**
	 * @var string
	 */
	private $defaultMetaValueColumn = 'meta_value';

	/**
	 * @param string|RawSQL $table
	 * @param string $metaKeyColumn
	 * @param string $metaValueColumn
	 *
	 * @return $this
	 */
	public function configureMetaTable( $table, $metaKeyColumn, $metaValueColumn ) {
		$this->metaTablesConfigs[] = new MetaTable(
			$table,
			$metaKeyColumn,
			$metaValueColumn
		);

		return $this;
	}

	/**
	 * @param string|RawSQL $table
	 *
	 * @return MetaTable
	 */
	protected function getMetaTable( $table ) {
		$tableName = QueryBuilder::prefixTable( $table );

		foreach ( $this->metaTablesConfigs as $metaTable ) {
			if ( $metaTable->tableName === $tableName ) {
				return $metaTable;
			}
		}

		return new MetaTable(
			$table,
			$this->defaultMetaKeyColumn,
			$this->defaultMetaValueColumn
		);
	}

	/**
	 * Select meta columns
	 *
	 * @since 1.0.0 optimize group concat functionality
	 * @since 1.0.0 add group concat functionality
	 * @since 1.0.0
	 *
	 * @param  string|RawSQL  $table
	 * @param  string  $foreignKey
	 * @param  string  $primaryKey
	 * @param  array|string  $columns
	 *
	 * @return $this
	 */
	public function attachMeta( $table, $foreignKey, $primaryKey, ...$columns ) {
		$metaTable = $this->getMetaTable( $table );

		foreach ( $columns as $i => $definition ) {
			if ( is_array( $definition ) ) {
				list( $column, $columnAlias, $concat ) = array_pad( $definition, 3, false );
			} else {
				$column = $definition;
				$columnAlias = $concat = false;
			}

			// Set dynamic alias
			$tableAlias = sprintf('%s_%s_%d', ( $table instanceof RawSQL ) ? $table->sql : $table, 'attach_meta', $i);

			// Check if we have meta columns that dev wants to group concat
			if ( $concat ) {
				/**
				 * Include foreign key to prevent errors if sql_mode is only_full_group_by
				 *
				 * @see https://dev.mysql.com/doc/refman/5.7/en/group-by-handling.html
				 */
				$this->groupBy( $foreignKey );

				// Group concat same key values into faux array
				// @example key => ["value1", "value2"]
				$this->selectRaw(
					"CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"',%1s,'\"')),']') AS %2s",
					$tableAlias . '.' . $metaTable->valueColumnName,
					$columnAlias ?: $column
				);
			} else {
				$this->select( [ "{$tableAlias}.{$metaTable->valueColumnName}", $columnAlias ?: $column ] );
			}

			$this->join(
				function ( JoinQueryBuilder $builder ) use (
					$table,
					$foreignKey,
					$primaryKey,
					$tableAlias,
					$column,
					$metaTable
				) {
					$builder
						->leftJoin( $table, $tableAlias )
						->on( $foreignKey, "{$tableAlias}.{$primaryKey}" )
						->andOn( "{$tableAlias}.{$metaTable->keyColumnName}", $column, true );
				}
			);
		}

		return $this;
	}
}
