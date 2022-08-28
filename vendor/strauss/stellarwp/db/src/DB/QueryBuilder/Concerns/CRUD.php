<?php
/**
 * @license GPL-2.0
 *
 * Modified by StellarWP on 28-August-2022 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Schema\StellarWP\DB\QueryBuilder\Concerns;

use StellarWP\Schema\StellarWP\DB\DB;

/**
 * @since 1.0.0
 */
trait CRUD {
	/**
	 * @see https://developer.wordpress.org/reference/classes/wpdb/insert/
	 *
	 * @since 1.0.0
	 *
	 * @param  array|string  $format
	 *
	 * @param  array  $data
	 * @return false|int
	 *
	 */
	public function insert( $data, $format = null ) {
		return DB::insert(
			$this->getTable(),
			$data,
			$format
		);
	}

	/**
	 * @see https://developer.wordpress.org/reference/classes/wpdb/update/
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $data
	 * @param  array|string|null  $format
	 *
	 * @return false|int
	 *
	 */
	public function update( $data, $format = null ) {
		return DB::update(
			$this->getTable(),
			$data,
			$this->getWhere(),
			$format,
			null
		);
	}

	/**
	 * @since 1.0.0
	 *
	 * @return false|int
	 *
	 * @see https://developer.wordpress.org/reference/classes/wpdb/delete/
	 */
	public function delete() {
		return DB::delete(
			$this->getTable(),
			$this->getWhere(),
			null
		);
	}

	/**
	 * Get results
	 *
	 * @since 1.0.0
	 *
	 * @param  string $output ARRAY_A|ARRAY_N|OBJECT|OBJECT_K
	 *
	 * @return array|object|null
	 */
	public function getAll( $output = OBJECT ) {
		return DB::get_results( $this->getSQL(), $output );
	}

	/**
	 * Get row
	 *
	 * @since 1.0.0
	 *
	 * @param  string $output ARRAY_A|ARRAY_N|OBJECT|OBJECT_K
	 *
	 * @return array|object|null
	 */
	public function get( $output = OBJECT ) {
		return DB::get_row( $this->getSQL(), $output );
	}

	/**
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function getTable() {
		return $this->froms[0]->table;
	}

	/**
	 * @since 1.0.0
	 *
	 * @return array[]
	 */
	private function getWhere() {
		$wheres = [];

		foreach ( $this->wheres as $where ) {
			$wheres[ $where->column ] = $where->value;
		}

		return $wheres;
	}
}
